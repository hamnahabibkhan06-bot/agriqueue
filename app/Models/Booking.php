<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public const FLOW = ['booked', 'checked_in', 'waiting', 'weighing', 'quality_check', 'unloading', 'payment_pending', 'completed'];
    public const ACTIVE = ['booked', 'checked_in', 'waiting', 'weighing', 'quality_check', 'unloading', 'payment_pending', 'completed', 'delayed'];
    public const IN_QUEUE = ['checked_in', 'waiting'];

    protected $guarded = [];
    protected $casts = ['booking_date' => 'date', 'checked_in_at' => 'datetime', 'queued_at' => 'datetime'];

    public function farmer() { return $this->belongsTo(User::class, 'farmer_id'); }
    public function center() { return $this->belongsTo(Center::class); }
    public function crop() { return $this->belongsTo(Crop::class); }
    public function procurement() { return $this->hasOne(Procurement::class); }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->queue_status));
    }

    /** 1-based position among farmers still waiting at the same center and day. */
    public function queuePosition(): ?int
    {
        if (!in_array($this->queue_status, self::IN_QUEUE, true)) return null;
        return static::where('center_id', $this->center_id)
            ->whereDate('booking_date', $this->booking_date)
            ->whereIn('queue_status', self::IN_QUEUE)
            ->where('checked_in_at', '<', $this->checked_in_at)
            ->count() + 1;
    }

    /** Estimated minutes until this vehicle reaches the weighing bridge. */
    public function etaMinutes(): ?int
    {
        $pos = $this->queuePosition();
        if ($pos === null) return null;
        $c = $this->center;
        return (int) (ceil(($pos - 1) / max(1, $c->weighing_stations)) * $c->avg_minutes_per_vehicle);
    }

    public function progressPercent(): int
    {
        $i = array_search($this->queue_status, self::FLOW, true);
        return $i === false ? 0 : (int) round(($i / (count(self::FLOW) - 1)) * 100);
    }
}
