<?php

namespace App\Observers;

use App\Models\Reservation;

class ReservationObserver
{
    public function updated(Reservation $reservation): void
    {
        // Future: could dispatch reminder job here
    }
}