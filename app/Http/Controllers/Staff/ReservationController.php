<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Jobs\SendReservationReminder;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index()
    {
        $user = Auth::guard('staff')->user();

        if (! $user->hasPermission('reservations.view')) {
            abort(403, 'You do not have permission to view reservations.');
        }

        $reservations = Reservation::with('user', 'table')->latest()->get();

        return view('staff.reservations.index', compact('reservations'));
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $user = Auth::guard('staff')->user();

        if (! $user->hasPermission('reservations.manage')) {
            abort(403, 'You do not have permission to manage reservations.');
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $updateData = ['status' => $request->status];
        $wasConfirmed = $reservation->status !== 'confirmed' && $request->status === 'confirmed';

        if ($request->status === 'confirmed') {
            $updateData['confirmed_at'] = now();
            $updateData['confirmed_by'] = $user->id;
        }

        if ($request->status === 'cancelled') {
            $updateData['cancellation_reason'] = $request->cancellation_reason;
        }

        $reservation->update($updateData);

        if ($wasConfirmed) {
            SendReservationReminder::dispatch($reservation->id);
        }

        return back()->with('success', 'Reservation status updated successfully.');
    }

    public function confirm(Reservation $reservation)
    {
        $user = Auth::guard('staff')->user();

        if (! $user->hasPermission('reservations.manage')) {
            abort(403, 'You do not have permission to manage reservations.');
        }

        if ($reservation->status === 'confirmed') {
            return back()->with('info', 'Reservation is already confirmed.');
        }

        $reservation->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $user->id,
        ]);

        // Dispatch reminder email asynchronously
        SendReservationReminder::dispatch($reservation->id);

        return back()->with('success', 'Reservation confirmed successfully.');
    }

    public function reject(Request $request, Reservation $reservation)
    {
        $user = Auth::guard('staff')->user();

        if (! $user->hasPermission('reservations.manage')) {
            abort(403, 'You do not have permission to manage reservations.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $reservation->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        return back()->with('success', 'Reservation rejected.');
    }
}
