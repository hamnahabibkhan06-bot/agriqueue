<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procurement extends Model
{
    protected $guarded = [];
    protected $casts = ['weighed_at' => 'datetime', 'completion_time' => 'datetime'];

    public function booking() { return $this->belongsTo(Booking::class); }
    public function inspector() { return $this->belongsTo(User::class, 'inspector_id'); }
}
