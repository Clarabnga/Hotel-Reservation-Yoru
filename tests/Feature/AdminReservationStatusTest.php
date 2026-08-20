<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReservationStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_confirm_then_complete_a_reservation(): void
    {
        $admin = User::factory()->admin()->create();
        $reservation = $this->reservation();

        $this->actingAs($admin)
            ->post(route('update.reservation', $reservation), ['status' => 'confirmed'])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('update.reservation', $reservation), ['status' => 'completed'])
            ->assertSessionHasNoErrors();

        $this->assertSame('completed', $reservation->refresh()->status);
    }

    public function test_invalid_and_disallowed_status_transitions_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $reservation = $this->reservation();

        $this->actingAs($admin)
            ->post(route('update.reservation', $reservation), ['status' => 'unknown'])
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)
            ->post(route('update.reservation', $reservation), ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $reservation->refresh()->status);
    }

    public function test_cancelled_reservation_is_terminal(): void
    {
        $admin = User::factory()->admin()->create();
        $reservation = $this->reservation(['status' => 'cancelled']);

        $this->actingAs($admin)
            ->post(route('update.reservation', $reservation), ['status' => 'confirmed'])
            ->assertSessionHasErrors('status');

        $this->assertSame('cancelled', $reservation->refresh()->status);
    }

    private function reservation(array $attributes = []): Reservation
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        return Reservation::create(array_merge([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '081234567890',
            'check_in' => '2026-09-10',
            'check_out' => '2026-09-12',
            'total_price' => $room->price,
            'status' => 'pending',
        ], $attributes));
    }
}
