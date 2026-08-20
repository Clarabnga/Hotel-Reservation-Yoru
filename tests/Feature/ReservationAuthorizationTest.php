<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_is_owned_by_the_authenticated_user_and_ignores_spoofed_identity(): void
    {
        $user = User::factory()->create([
            'name' => 'Real Customer',
            'email' => 'customer@example.com',
        ]);
        $room = Room::factory()->create(['status' => 'available']);

        $response = $this->actingAs($user)->post('/booking', [
            'room_id' => $room->id,
            'name' => 'Spoofed Name',
            'email' => 'spoofed@example.com',
            'phone' => '+62 812 3456 7890',
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
        ]);

        $reservation = Reservation::sole();

        $response->assertRedirect(route('receipt', $reservation));
        $this->assertSame($user->id, $reservation->user_id);
        $this->assertSame('Real Customer', $reservation->name);
        $this->assertSame('customer@example.com', $reservation->email);
    }

    public function test_customer_can_view_their_own_receipt(): void
    {
        $user = User::factory()->create();
        $reservation = $this->reservationFor($user);

        $this->actingAs($user)
            ->get(route('receipt', $reservation))
            ->assertOk()
            ->assertSee($reservation->email);
    }

    public function test_customer_cannot_view_another_customers_receipt(): void
    {
        $owner = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $reservation = $this->reservationFor($owner);

        $this->actingAs($otherCustomer)
            ->get(route('receipt', $reservation))
            ->assertForbidden();
    }

    public function test_admin_can_view_a_customers_receipt(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $reservation = $this->reservationFor($owner);

        $this->actingAs($admin)
            ->get(route('receipt', $reservation))
            ->assertOk();
    }

    public function test_guest_cannot_view_a_receipt_or_create_a_booking(): void
    {
        $owner = User::factory()->create();
        $reservation = $this->reservationFor($owner);

        $this->get(route('receipt', $reservation))->assertRedirect(route('login'));
        $this->post('/booking')->assertRedirect(route('login'));
    }

    private function reservationFor(User $user): Reservation
    {
        $room = Room::factory()->create();

        return Reservation::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '081234567890',
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'total_price' => $room->price,
            'status' => 'pending',
        ]);
    }
}
