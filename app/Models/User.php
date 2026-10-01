<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'location', 'identification', 'registration_status', 'center_id'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'email_verified_at' => 'datetime'];
    }

    public function center() { return $this->belongsTo(Center::class); }
    public function bookings() { return $this->hasMany(Booking::class, 'farmer_id'); }
    public function notices() { return $this->hasMany(Notice::class)->latest(); }
    public function hasRole(string ...$roles): bool { return in_array($this->role, $roles, true); }
    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'admin' => 'admin.dashboard',
            'staff' => 'staff.queue',
            'inspector' => 'inspector.index',
            default => 'farmer.dashboard',
        };
    }
}
