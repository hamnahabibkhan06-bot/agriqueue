<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];
}
