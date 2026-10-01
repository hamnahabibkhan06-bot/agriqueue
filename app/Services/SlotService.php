<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Center;
use Carbon\Carbon;

class SlotService
{
    public const SLOTS = ['08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-16:00'];

    private function counted(Center $c, string $date)
    {
        // Cancelled, missed and rejected bookings free their capacity.
        return Booking::where('center_id', $c->id)->whereDate('booking_date', $date)->whereIn('queue_status', Booking::ACTIVE);
    }

    public function dailyBooked(Center $c, string $date): float
    {
        return (float) $this->counted($c, $date)->sum('estimated_quantity');
    }

    /** Slot table with capacity, booked, remaining, status and a best-slot recommendation. */
    public function slots(Center $c, string $date, float $qty = 0): array
    {
        $booked = $this->counted($c, $date)->selectRaw('time_slot, SUM(estimated_quantity) as t')->groupBy('time_slot')->pluck('t', 'time_slot');
        $dailyLeft = (float) $c->daily_capacity - $this->dailyBooked($c, $date);
        $rows = [];
        foreach (self::SLOTS as $slot) {
            $b = (float) ($booked[$slot] ?? 0);
            $remaining = max(0, (float) $c->slot_capacity - $b);
            $past = Carbon::parse($date)->isToday() && Carbon::parse(substr($slot, 0, 5))->isPast();
            $fits = !$past && $qty > 0 && $qty <= $remaining && $qty <= $dailyLeft;
            $status = $past ? 'Closed' : ($remaining <= 0 ? 'Full' : ($remaining < (float) $c->slot_capacity * 0.25 ? 'Almost full' : 'Available'));
            $rows[] = ['slot' => $slot, 'capacity' => (float) $c->slot_capacity, 'booked' => $b, 'remaining' => $remaining, 'status' => $status, 'fits' => $fits || ($qty == 0 && !$past && $remaining > 0), 'recommended' => false];
        }
        // Recommend the slot with the most free capacity (least crowded), earliest on ties.
        $best = null;
        foreach ($rows as $i => $r) {
            if ($r['fits'] && ($best === null || $r['remaining'] > $rows[$best]['remaining'])) $best = $i;
        }
        if ($best !== null) $rows[$best]['recommended'] = true;
        return ['slots' => $rows, 'daily_capacity' => (float) $c->daily_capacity, 'daily_booked' => $this->dailyBooked($c, $date), 'daily_remaining' => max(0, $dailyLeft)];
    }

    /** Returns an error message, or null when the booking is allowed. Call inside a transaction. */
    public function validate(Center $c, string $date, string $slot, float $qty): ?string
    {
        if (!in_array($slot, self::SLOTS, true)) return 'Choose a valid time slot.';
        if ($this->dailyBooked($c, $date) + $qty > (float) $c->daily_capacity) return 'The daily limit for this center has been reached. Try another date or center.';
        $slotBooked = (float) $this->counted($c, $date)->where('time_slot', $slot)->sum('estimated_quantity');
        if ($slotBooked + $qty > (float) $c->slot_capacity) return 'This slot is full or too small for your quantity. Pick another slot.';
        return null;
    }

    public function nextToken(Center $c, string $date): string
    {
        $seq = Booking::where('center_id', $c->id)->whereDate('booking_date', $date)->count() + 1;
        return sprintf('AP-%d-%s-%03d', $c->id, Carbon::parse($date)->format('md'), $seq);
    }
}
