<?php

namespace Tests\Unit;

use App\Jobs\SendReservationReminder;
use App\Models\CafeTable;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Pest\BeforeEach;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->table = CafeTable::factory()->create();
    $this->reservation = Reservation::factory()->create([
        'user_id' => $this->user->id,
        'table_id' => $this->table->id,
        'status' => 'confirmed',
    ]);
});

it('sends reminder email for confirmed reservation', function () {
    Mail::fake();

    $job = new SendReservationReminder($this->reservation->id);
    $job->handle();

    Mail::assertSent(\App\Mail\ReservationReminder::class);
});

it('skips email for non-confirmed reservations', function () {
    Mail::fake();

    $this->reservation->update(['status' => 'pending']);

    $job = new SendReservationReminder($this->reservation->id);
    $job->handle();

    Mail::assertNotSent(\App\Mail\ReservationReminder::class);
});

it('skips email when reservation not found', function () {
    Mail::fake();

    $job = new SendReservationReminder(99999);
    $job->handle();

    Mail::assertNothingSent();
});