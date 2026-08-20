<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_overlapping_reservation_is_rejected_when_no_same_type_room_is_available(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['type' => 'Only Type']);
        $this->reservationFor($user, $room, '2026-09-10', '2026-09-15');

        $this->actingAs($user)
            ->from(route('booking.form', $room))
            ->post(route('booking.store'), $this->bookingData($room, '2026-09-12', '2026-09-14'))
            ->assertRedirect(route('booking.form', $room))
            ->assertSessionHas('error');

        $this->assertSame(1, Reservation::count());
    }

    public function test_reservation_that_contains_an_existing_stay_is_detected_as_overlap(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['type' => 'Containing Type']);
        $this->reservationFor($user, $room, '2026-09-12', '2026-09-14');

        $this->actingAs($user)
            ->post(route('booking.store'), $this->bookingData($room, '2026-09-10', '2026-09-16'))
            ->assertSessionHas('error');

        $this->assertSame(1, Reservation::count());
    }

    public function test_adjacent_date_ranges_are_allowed(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['type' => 'Adjacent Type']);
        $this->reservationFor($user, $room, '2026-09-10', '2026-09-12');

        $this->actingAs($user)
            ->post(route('booking.store'), $this->bookingData($room, '2026-09-12', '2026-09-14'))
            ->assertRedirect();

        $this->assertSame(2, Reservation::count());
    }

    public function test_cancelled_reservation_does_not_block_availability(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['type' => 'Cancelled Type']);
        $this->reservationFor($user, $room, '2026-09-10', '2026-09-15', 'cancelled');

        $this->actingAs($user)
            ->post(route('booking.store'), $this->bookingData($room, '2026-09-11', '2026-09-13'))
            ->assertRedirect();

        $this->assertSame(2, Reservation::count());
    }

    public function test_same_type_alternative_allocation_is_deterministic(): void
    {
        $user = User::factory()->create();
        $requested = Room::factory()->create(['type' => 'Shared Type']);
        $firstAlternative = Room::factory()->create(['type' => 'Shared Type']);
        Room::factory()->create(['type' => 'Shared Type']);
        $this->reservationFor($user, $requested, '2026-09-10', '2026-09-15');

        $this->actingAs($user)
            ->post(route('booking.store'), $this->bookingData($requested, '2026-09-11', '2026-09-13'))
            ->assertRedirect();

        $this->assertSame($firstAlternative->id, Reservation::latest('id')->firstOrFail()->room_id);
    }

    public function test_inactive_and_maintenance_rooms_are_not_allocated(): void
    {
        $user = User::factory()->create();
        $inactive = Room::factory()->create([
            'type' => 'Closed Type',
            'operational_status' => 'inactive',
        ]);
        Room::factory()->create([
            'type' => 'Closed Type',
            'operational_status' => 'maintenance',
        ]);

        $this->actingAs($user)
            ->post(route('booking.store'), $this->bookingData($inactive, '2026-09-11', '2026-09-13'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('reservations', 0);
    }

    /**
     * @return array<string, int|string>
     */
    private function bookingData(Room $room, string $checkIn, string $checkOut): array
    {
        return [
            'room_id' => $room->id,
            'phone' => '081234567890',
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ];
    }

    private function reservationFor(
        User $user,
        Room $room,
        string $checkIn,
        string $checkOut,
        string $status = 'confirmed',
    ): Reservation {
        return Reservation::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '081234567890',
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'total_price' => $room->price,
            'status' => $status,
        ]);
    }
}
