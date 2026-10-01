<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Crop;
use App\Models\Procurement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $today = today()->toDateString();
        $todayB = Booking::whereDate('booking_date', $today);
        $done = Procurement::with('booking.crop', 'booking.center')->whereNotNull('completion_time')->get();
        $from = today()->subDays(13);

        $waits = Procurement::with('booking')->whereNotNull('weighed_at')->get()
            ->filter(fn ($p) => $p->booking && $p->booking->queued_at)->map(fn ($p) => abs($p->booking->queued_at->diffInMinutes($p->weighed_at)));
        $proc = $done->filter(fn ($p) => $p->booking->checked_in_at)->map(fn ($p) => abs($p->booking->checked_in_at->diffInMinutes($p->completion_time)));

        $capTotal = (float) Center::where('is_active', true)->sum('daily_capacity');
        $bookedToday = (float) (clone $todayB)->whereIn('queue_status', Booking::ACTIVE)->sum('estimated_quantity');

        $stats = [
            'expected' => (clone $todayB)->whereIn('queue_status', ['booked', 'delayed'])->count(),
            'waiting' => (clone $todayB)->whereIn('queue_status', Booking::IN_QUEUE)->count(),
            'completed' => (clone $todayB)->where('queue_status', 'completed')->count(),
            'tons' => round($done->sum('actual_weight') / 1000, 1),
            'avg_wait' => round($waits->avg() ?? 0),
            'avg_process' => round($proc->avg() ?? 0),
            'usage' => $capTotal ? round($bookedToday / $capTotal * 100) : 0,
            'rejected' => Booking::where('queue_status', 'rejected')->count(),
            'pay_pending' => Procurement::where('payment_status', 'pending')->whereNotNull('total_amount')->count(),
            'top_crop' => $done->groupBy(fn ($p) => $p->booking->crop->name)->map(fn ($g) => $g->sum('actual_weight'))->sortDesc()->keys()->first() ?? '—',
        ];

        $days = collect(range(0, 13))->map(fn ($i) => $from->copy()->addDays($i)->toDateString());
        $byDay = $done->groupBy(fn ($p) => $p->booking->booking_date->toDateString())->map(fn ($g) => round($g->sum('actual_weight') / 1000, 1));
        $charts = [
            'days' => $days->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'))->values(),
            'trend' => $days->map(fn ($d) => $byDay[$d] ?? 0)->values(),
            'centers' => $done->groupBy(fn ($p) => $p->booking->center->name)->map(fn ($g) => round($g->sum('actual_weight') / 1000, 1)),
            'grades' => Procurement::whereNotNull('quality_grade')->selectRaw('quality_grade, count(*) c')->groupBy('quality_grade')->pluck('c', 'quality_grade'),
            'peak' => Booking::whereIn('queue_status', Booking::ACTIVE)->selectRaw('time_slot, count(*) c')->groupBy('time_slot')->pluck('c', 'time_slot'),
            'crops' => $done->groupBy(fn ($p) => $p->booking->crop->name)->map(fn ($g) => round($g->sum('actual_weight') / 1000, 1)),
        ];

        $centers = Center::where('is_active', true)->get()->map(function ($c) use ($today) {
            $booked = (float) Booking::where('center_id', $c->id)->whereDate('booking_date', $today)->whereIn('queue_status', Booking::ACTIVE)->sum('estimated_quantity');
            return ['c' => $c, 'booked' => $booked, 'pct' => $c->daily_capacity ? min(100, round($booked / $c->daily_capacity * 100)) : 0,
                'queue' => Booking::where('center_id', $c->id)->whereDate('booking_date', $today)->whereIn('queue_status', Booking::IN_QUEUE)->count()];
        });
        $stats['avg_queue'] = round($centers->avg('queue') ?? 0, 1);

        return view('admin.dashboard', compact('stats', 'charts', 'centers'));
    }

    /* ---------- Centers ---------- */
    public function centers() { return view('admin.centers', ['centers' => Center::withCount('bookings')->get()]); }

    private function centerRules(): array
    {
        return [
            'name' => 'required|string|max:100', 'location' => 'required|string|max:150',
            'daily_capacity' => 'required|numeric|min:1', 'slot_capacity' => 'required|numeric|min:1',
            'weighing_stations' => 'required|integer|min:1|max:20', 'unloading_points' => 'required|integer|min:1|max:30',
            'inspection_counters' => 'required|integer|min:1|max:20', 'storage_capacity' => 'required|numeric|min:0',
            'staff_available' => 'required|integer|min:0|max:200', 'avg_minutes_per_vehicle' => 'required|integer|min:1|max:240',
        ];
    }

    public function centerStore(Request $r)
    {
        $c = Center::create($r->validate($this->centerRules()) + ['image' => 'https://loremflickr.com/800/500/grain,warehouse?lock=' . rand(1, 90)]);
        ActivityLog::record('center.created', $c->name);
        return back()->with('ok', 'Procurement center added.');
    }

    public function centerUpdate(Request $r, Center $center)
    {
        $center->update($r->validate($this->centerRules()) + ['is_active' => $r->boolean('is_active')]);
        ActivityLog::record('center.updated', $center->name);
        return back()->with('ok', "{$center->name} updated.");
    }

    /* ---------- Crops ---------- */
    public function crops() { return view('admin.crops', ['crops' => Crop::all()]); }

    public function cropStore(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:60|unique:crops,name', 'price_per_ton' => 'required|numeric|min:1']);
        Crop::create($d + ['image' => 'https://loremflickr.com/600/400/' . urlencode(strtolower($d['name'])) . ',crop?lock=' . rand(1, 90)]);
        ActivityLog::record('crop.created', $d['name']);
        return back()->with('ok', 'Crop category added.');
    }

    public function cropUpdate(Request $r, Crop $crop)
    {
        $d = $r->validate(['name' => ['required', 'string', 'max:60', Rule::unique('crops', 'name')->ignore($crop->id)], 'price_per_ton' => 'required|numeric|min:1']);
        $crop->update($d + ['is_active' => $r->boolean('is_active')]);
        ActivityLog::record('crop.updated', $crop->name);
        return back()->with('ok', 'Crop updated. New price applies to future procurements.');
    }

    /* ---------- Users ---------- */
    public function users(Request $r)
    {
        $q = User::with('center')->latest();
        if ($r->filled('role')) $q->where('role', $r->role);
        if ($r->filled('q')) $q->where(fn ($w) => $w->where('name', 'like', "%{$r->q}%")->orWhere('email', 'like', "%{$r->q}%"));
        return view('admin.users', ['users' => $q->paginate(12)->withQueryString(), 'centers' => Center::all()]);
    }

    public function userStore(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|string|max:100', 'email' => 'required|email|unique:users,email',
            'role' => 'required|in:staff,inspector,admin', 'center_id' => 'nullable|exists:centers,id',
            'password' => 'required|min:8',
        ]);
        $u = User::create($d + ['registration_status' => 'verified']);
        ActivityLog::record('user.created', "{$u->email} ({$u->role})");
        return back()->with('ok', ucfirst($u->role) . ' account created.');
    }

    public function userUpdate(Request $r, User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'You cannot change your own account here.');
        $d = $r->validate(['registration_status' => 'required|in:verified,suspended', 'center_id' => 'nullable|exists:centers,id']);
        $user->update($d);
        ActivityLog::record('user.updated', "{$user->email}: {$d['registration_status']}");
        return back()->with('ok', 'User updated.');
    }

    /* ---------- Monitoring ---------- */
    public function bookings(Request $r)
    {
        $q = Booking::with(['farmer', 'center', 'crop', 'procurement'])->latest('booking_date')->latest('id');
        if ($r->filled('center_id')) $q->where('center_id', $r->center_id);
        if ($r->filled('status')) $q->where('queue_status', $r->status);
        if ($r->filled('date')) $q->whereDate('booking_date', $r->date);
        return view('admin.bookings', ['bookings' => $q->paginate(15)->withQueryString(), 'centers' => Center::all()]);
    }

    public function logs() { return view('admin.logs', ['logs' => ActivityLog::with('user')->latest()->paginate(25)]); }
}
