<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Crop;
use App\Models\Notice;
use App\Services\SlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FarmerController extends Controller
{
    public function __construct(private SlotService $slots) {}

    private function own(Booking $b): Booking
    {
        abort_unless($b->farmer_id === auth()->id(), 403);
        return $b->load(['center', 'crop', 'procurement']);
    }

    public function dashboard()
    {
        $u = auth()->user();
        $all = $u->bookings()->with(['center', 'crop', 'procurement']);
        return view('farmer.dashboard', [
            'current' => (clone $all)->whereNotIn('queue_status', ['completed', 'cancelled', 'missed', 'rejected'])->whereDate('booking_date', '>=', today())->orderBy('booking_date')->orderBy('time_slot')->first(),
            'upcoming' => (clone $all)->whereIn('queue_status', ['booked', 'delayed'])->whereDate('booking_date', '>=', today())->orderBy('booking_date')->limit(4)->get(),
            'totals' => [
                'bookings' => $u->bookings()->count(),
                'completed' => $u->bookings()->where('queue_status', 'completed')->count(),
                'tons' => round(\App\Models\Procurement::whereIn('booking_id', $u->bookings()->pluck('id'))->whereNotNull('completion_time')->sum('actual_weight') / 1000, 2),
                'pending' => \App\Models\Procurement::whereIn('booking_id', $u->bookings()->pluck('id'))->where('payment_status', 'pending')->whereNotNull('total_amount')->sum('total_amount'),
            ],
            'notices' => $u->notices()->limit(5)->get(),
        ]);
    }

    public function index(Request $r)
    {
        $q = auth()->user()->bookings()->with(['center', 'crop', 'procurement'])->latest('booking_date');
        if ($r->filled('center_id')) $q->where('center_id', $r->center_id);
        if ($r->filled('crop_id')) $q->where('crop_id', $r->crop_id);
        if ($r->filled('date')) $q->whereDate('booking_date', $r->date);
        if ($r->filled('status')) $q->where('queue_status', $r->status);
        if ($r->filled('payment')) $q->whereHas('procurement', fn ($p) => $p->where('payment_status', $r->payment));
        return view('farmer.bookings', ['bookings' => $q->paginate(10)->withQueryString(), 'centers' => Center::all(), 'crops' => Crop::all()]);
    }

    public function create()
    {
        return view('farmer.create', ['centers' => Center::where('is_active', true)->get(), 'crops' => Crop::where('is_active', true)->get()]);
    }

    /** JSON for the live slot table on the booking form. */
    public function slotTable(Request $r)
    {
        $r->validate(['center_id' => 'required|exists:centers,id', 'date' => 'required|date', 'qty' => 'nullable|numeric|min:0']);
        return response()->json($this->slots->slots(Center::findOrFail($r->center_id), $r->date, (float) $r->qty));
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'crop_id' => 'required|exists:crops,id',
            'center_id' => 'required|exists:centers,id',
            'estimated_quantity' => 'required|numeric|min:0.5|max:60',
            'vehicle_number' => 'nullable|string|max:20',
            'booking_date' => 'required|date|after_or_equal:today|before:+31 days',
            'time_slot' => 'required|string',
        ]);

        try {
            $booking = DB::transaction(function () use ($d) {
                // Lock the center row so two farmers cannot take the last tons at the same moment.
                $center = Center::lockForUpdate()->findOrFail($d['center_id']);
                $dup = Booking::where('farmer_id', auth()->id())->where('crop_id', $d['crop_id'])
                    ->whereDate('booking_date', $d['booking_date'])->where('time_slot', $d['time_slot'])
                    ->whereIn('queue_status', Booking::ACTIVE)->exists();
                if ($dup) return 'You already have a booking for this crop in this slot.';
                if ($err = $this->slots->validate($center, $d['booking_date'], $d['time_slot'], (float) $d['estimated_quantity'])) return $err;

                return Booking::create($d + [
                    'farmer_id' => auth()->id(),
                    'token_number' => $this->slots->nextToken($center, $d['booking_date']),
                    'queue_status' => 'booked',
                ]);
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->withErrors(['time_slot' => 'We could not save your booking. Please try again.']);
        }

        if (is_string($booking)) return back()->withInput()->withErrors(['time_slot' => $booking]);

        Notice::send(auth()->id(), "Booking confirmed for {$booking->booking_date->format('d M')} at {$booking->time_slot}.");
        Notice::send(auth()->id(), "Your digital token is {$booking->token_number}. Show it at the gate.");
        ActivityLog::record('booking.created', $booking->token_number);
        return redirect()->route('farmer.bookings.show', $booking)->with('ok', 'Slot booked. Your digital token is ready.');
    }

    public function show(Booking $booking)
    {
        return view('farmer.show', ['b' => $this->own($booking)]);
    }

    public function status(Booking $booking)
    {
        $b = $this->own($booking);
        return response()->json(['status' => $b->queue_status, 'label' => $b->label(), 'position' => $b->queuePosition(), 'eta' => $b->etaMinutes(), 'progress' => $b->progressPercent()]);
    }

    public function cancel(Booking $booking)
    {
        $b = $this->own($booking);
        if (!in_array($b->queue_status, ['booked', 'delayed'], true)) return back()->withErrors(['cancel' => 'Only bookings that have not arrived can be cancelled.']);
        $b->update(['queue_status' => 'cancelled']);
        Notice::send(auth()->id(), "Booking {$b->token_number} was cancelled.");
        ActivityLog::record('booking.cancelled', $b->token_number);
        return redirect()->route('farmer.bookings')->with('ok', 'Booking cancelled. Your slot is open for others.');
    }

    public function history()
    {
        $rows = auth()->user()->bookings()->with(['center', 'crop', 'procurement'])->where('queue_status', 'completed')->latest('booking_date')->paginate(10);
        return view('farmer.history', ['rows' => $rows]);
    }

    public function notices()
    {
        $n = auth()->user()->notices()->paginate(20);
        auth()->user()->notices()->where('is_read', false)->update(['is_read' => true]);
        return view('farmer.notices', ['notices' => $n]);
    }
}
