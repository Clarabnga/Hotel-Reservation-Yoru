<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->role === 'admin' || $reservation->user_id === $user->id;
    }

    public function cancel(User $user, Reservation $reservation): bool
    {
        return $reservation->user_id === $user->id
            && in_array($reservation->status, ['pending', 'confirmed'], true)
            && $reservation->check_in > now()->toDateString();
    }
}
