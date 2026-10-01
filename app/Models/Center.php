<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Center extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function bookings() { return $this->hasMany(Booking::class); }
}
