<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = 'app_notices';
    protected $guarded = [];
    protected $casts = ['is_read' => 'boolean'];

    public static function send(int $userId, string $message): void
    {
        static::create(['user_id' => $userId, 'message' => $message]);
    }
}
