<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Mail\ReservationReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReservationReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $reservationId
    ) {}

    public function handle(): void
    {
        $reservation = Reservation::with(['user', 'table'])->find($this->reservationId);

        if (! $reservation) {
            Log::warning('SendReservationReminder: reservation not found', [
                'id' => $this->reservationId,
            ]);

            return;
        }

        if ($reservation->status !== 'confirmed') {
            Log::info('SendReservationReminder: reservation not confirmed, skipping', [
                'id' => $reservation->id,
                'status' => $reservation->status,
            ]);

            return;
        }

        try {
            if ($reservation->user && $reservation->user->email) {
                Mail::to($reservation->user->email)->send(new ReservationReminder($reservation));

                Log::info('SendReservationReminder: email sent', [
                    'reservation_id' => $reservation->id,
                    'email' => $reservation->user->email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('SendReservationReminder: failed to send', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}