<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Center;
use App\Models\Crop;
use App\Models\Procurement;

class PublicController extends Controller
{
    public function home()
    {
        return view('home', [
            'crops' => Crop::where('is_active', true)->get(),
            'centers' => Center::where('is_active', true)->count(),
            'tons' => round(Procurement::whereNotNull('completion_time')->sum('actual_weight') / 1000, 1),
            'farmers' => \App\Models\User::where('role', 'farmer')->count(),
        ]);
    }

    /** Public TV screen: tokens only, no personal data. */
    public function board(Center $center)
    {
        return view('board', ['center' => $center]);
    }

    public function boardData(Center $center)
    {
        $today = today()->toDateString();
        $q = fn ($s) => Booking::where('center_id', $center->id)->whereDate('booking_date', $today)->whereIn('queue_status', (array) $s)->orderBy('checked_in_at')->limit(8)->pluck('token_number');
        return response()->json([
            'weighing' => $q('weighing'), 'waiting' => $q(Booking::IN_QUEUE),
            'quality' => $q('quality_check'), 'unloading' => $q('unloading'),
            'updated' => now()->format('H:i:s'),
        ]);
    }
}
