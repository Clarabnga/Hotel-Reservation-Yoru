<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_history_only_contains_their_reservations(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $own = $this->reservationFor($user, ['name' => 'Own Reservation']);
        $otherReservation = $this->reservationFor($other, ['name' => 'Other Reservation']);

        $this->actingAs($user)
            ->get(route('reservations.index'))
            ->assertOk()
            ->assertSee(route('receipt', $own), false)
            ->assertDontSee(route('receipt', $otherReservation), false);
    }

    public function test_owner_can_cancel_an_upcoming_reservation(): void
    {
        $user = User::factory()->create();
        $reservation = $this->reservationFor($user);

        $this->actingAs($user)
            ->post(route('reservations.cancel', $reservation))
            ->assertRedirect(route('reservations.index'));

        $this->assertSame('cancelled', $reservation->refresh()->status);
    }

    public function test_other_customer_and_late_cancellation_are_forbidden(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $future = $this->reservationFor($owner);
        $past = $this->reservationFor($owner, [
            'check_in' => now()->subDays(2)->toDateString(),
            'check_out' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($other)->post(route('reservations.cancel', $future))->assertForbidden();
        $this->actingAs($owner)->post(route('reservations.cancel', $past))->assertForbidden();
    }

    private function reservationFor(User $user, array $attributes = []): Reservation
    {
        $room = Room::factory()->create();

        return Reservation::create(array_merge([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '081234567890',
            'check_in' => now()->addDays(2)->toDateString(),
            'check_out' => now()->addDays(4)->toDateString(),
            'total_price' => $room->price * 2,
            'status' => 'pending',
        ], $attributes));
    }
}
