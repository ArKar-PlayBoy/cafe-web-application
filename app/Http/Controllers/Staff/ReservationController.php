<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index()
    {
        $user = Auth::guard('staff')->user();
        
        if (!$user->hasPermission('reservations.view')) {
            abort(403, 'You do not have permission to view reservations.');
        }

        $reservations = Reservation::with('user', 'table')->latest()->get();

        return view('staff.reservations.index', compact('reservations'));
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $user = Auth::guard('staff')->user();
        
        if (!$user->hasPermission('reservations.manage')) {
            abort(403, 'You do not have permission to manage reservations.');
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $reservation->update(['status' => $request->status]);

        return back()->with('success', 'Reservation status updated successfully.');
    }
}
