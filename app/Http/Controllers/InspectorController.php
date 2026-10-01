<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Notice;
use Illuminate\Http\Request;

class InspectorController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $q = Booking::with(['farmer', 'crop', 'center', 'procurement'])->where('queue_status', 'quality_check');
        if ($u->center_id) $q->where('center_id', $u->center_id);
        $recent = \App\Models\Procurement::with('booking.crop')->where('inspector_id', $u->id)->latest()->limit(8)->get();
        return view('inspector.index', ['pending' => $q->orderBy('updated_at')->get(), 'recent' => $recent]);
    }

    public function store(Request $r, Booking $booking)
    {
        $u = $r->user();
        abort_unless(!$u->center_id || $u->center_id === $booking->center_id, 403);
        abort_unless($booking->queue_status === 'quality_check', 422);
        $d = $r->validate([
            'quality_grade' => 'required|in:A,B,C',
            'moisture' => 'required|numeric|min:0|max:60',
            'damaged_percent' => 'nullable|numeric|min:0|max:100',
            'foreign_material' => 'nullable|numeric|min:0|max:100',
            'inspector_remarks' => 'nullable|string|max:500',
            'decision' => 'required|in:accepted,rejected',
        ]);
        $decision = $d['decision'];
        unset($d['decision']);

        $booking->procurement->update($d + ['quality_status' => $decision, 'inspector_id' => $u->id]);
        $booking->update(['queue_status' => $decision === 'accepted' ? 'unloading' : 'rejected']);

        Notice::send($booking->farmer_id, $decision === 'accepted'
            ? "Quality check passed (Grade {$d['quality_grade']}). Please proceed to unloading."
            : 'Your produce was rejected at quality check. See the inspector remarks on your booking.');
        ActivityLog::record('quality.' . $decision, $booking->token_number);
        return back()->with('ok', "{$booking->token_number} {$decision}.");
    }
}
