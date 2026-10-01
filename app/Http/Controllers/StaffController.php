<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Notice;
use App\Models\Procurement;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public const GRADE_FACTOR = ['A' => 1.0, 'B' => 0.95, 'C' => 0.90];

    private function center(Request $r): Center
    {
        $u = $r->user();
        if ($u->role === 'admin') return Center::findOrFail($r->query('center', Center::value('id')));
        abort_unless($u->center_id, 403, 'You are not assigned to a procurement center yet.');
        return Center::findOrFail($u->center_id);
    }

    private function guard(Booking $b): void
    {
        $u = auth()->user();
        abort_unless($u->role === 'admin' || $u->center_id === $b->center_id, 403);
    }

    public function queue(Request $r)
    {
        $center = $this->center($r);
        $q = Booking::with(['farmer', 'crop', 'procurement'])->where('center_id', $center->id)->whereDate('booking_date', today());
        if ($r->filled('token')) $q->where('token_number', 'like', '%' . trim($r->token) . '%');
        $all = $q->orderBy('time_slot')->orderBy('checked_in_at')->get();
        return view('staff.queue', [
            'center' => $center, 'centers' => Center::all(),
            'groups' => $all->groupBy('queue_status'),
            'count' => $all->count(), 'search' => $r->token,
        ]);
    }

    public function advance(Request $r, Booking $booking)
    {
        $this->guard($booking);
        $s = $booking->queue_status;
        $msg = null;

        if (in_array($s, ['booked', 'delayed'], true)) {
            $booking->update(['queue_status' => 'checked_in', 'checked_in_at' => now()]);
            $msg = "You are checked in at {$booking->center->name}. Token {$booking->token_number} is verified.";
        } elseif ($s === 'checked_in') {
            $booking->update(['queue_status' => 'waiting', 'queued_at' => now()]);
            $msg = 'You have joined the live queue. Your position: ' . $booking->fresh()->queuePosition() . '.';
        } elseif ($s === 'waiting') {
            $booking->update(['queue_status' => 'weighing']);
            $msg = 'Please proceed to the weighing station now.';
        } elseif ($s === 'unloading') {
            $p = $booking->procurement;
            abort_unless($p && $p->actual_weight, 422);
            $price = (float) $booking->crop->price_per_ton * (self::GRADE_FACTOR[$p->quality_grade] ?? 1);
            $p->update(['price_per_unit' => round($price, 2), 'total_amount' => round($price * ($p->actual_weight / 1000), 2)]);
            $booking->update(['queue_status' => 'payment_pending']);
            $msg = 'Unloading complete. Payment of Rs ' . number_format($p->total_amount) . ' is pending.';
        } elseif ($s === 'payment_pending') {
            $p = $booking->procurement;
            $p->update(['payment_status' => 'paid', 'completion_time' => now(), 'receipt_number' => 'RC-' . now()->format('ymd') . '-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT)]);
            $booking->update(['queue_status' => 'completed']);
            $msg = "Procurement complete. Payment received. Receipt {$p->receipt_number} is ready.";
        } else {
            return back()->withErrors(['action' => 'That step is not available for this booking.']);
        }

        Notice::send($booking->farmer_id, $msg);
        ActivityLog::record('queue.' . $booking->queue_status, $booking->token_number);
        return back()->with('ok', "{$booking->token_number} moved to " . $booking->fresh()->label() . '.');
    }

    public function weigh(Request $r, Booking $booking)
    {
        $this->guard($booking);
        abort_unless($booking->queue_status === 'weighing', 422);
        $d = $r->validate([
            'gross_weight' => 'required|numeric|min:1',
            'empty_weight' => 'required|numeric|min:1|lt:gross_weight',
            'weighing_station' => 'required|string|max:20',
        ], ['empty_weight.lt' => 'Empty vehicle weight must be less than gross weight.']);

        Procurement::updateOrCreate(['booking_id' => $booking->id], $d + [
            'actual_weight' => $d['gross_weight'] - $d['empty_weight'], 'weighed_at' => now(),
        ]);
        $booking->update(['queue_status' => 'quality_check']);
        Notice::send($booking->farmer_id, 'Weighing done: ' . number_format($d['gross_weight'] - $d['empty_weight']) . ' kg net. Your crop is now at quality check.');
        ActivityLog::record('weighing.recorded', $booking->token_number);
        return back()->with('ok', 'Weight recorded. Sent to the quality inspector.');
    }

    public function flag(Request $r, Booking $booking)
    {
        $this->guard($booking);
        $status = $r->validate(['status' => 'required|in:missed,delayed'])['status'];
        if (!in_array($booking->queue_status, ['booked', 'delayed'], true)) return back()->withErrors(['action' => 'Only bookings that have not checked in can be flagged.']);
        $booking->update(['queue_status' => $status]);
        Notice::send($booking->farmer_id, $status === 'missed' ? "Your slot {$booking->time_slot} was missed. Please book again." : "Your booking {$booking->token_number} is marked delayed. Please arrive soon.");
        ActivityLog::record('booking.' . $status, $booking->token_number);
        return back()->with('ok', "{$booking->token_number} marked {$status}.");
    }

    public function receipt(Booking $booking)
    {
        $u = auth()->user();
        abort_unless($u->role === 'admin' || $u->id === $booking->farmer_id || $u->center_id === $booking->center_id, 403);
        abort_unless($booking->queue_status === 'completed', 404);
        return view('receipt', ['b' => $booking->load(['farmer', 'center', 'crop', 'procurement.inspector'])]);
    }
}
