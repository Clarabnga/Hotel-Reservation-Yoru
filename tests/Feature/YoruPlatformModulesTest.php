<?php

namespace Tests\Feature;

use App\Jobs\ProcessGuestServiceRequest;
use App\Models\BusinessInquiry;
use App\Models\GuestServiceRequest;
use App\Models\PriorityQueue;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YoruPlatformModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_room_catalog_shows_products_not_physical_room_numbers(): void
    {
        $type = RoomType::create(['name' => 'Deluxe King', 'slug' => 'deluxe-king', 'capacity' => 2, 'bed_type' => 'King Bed', 'active' => true]);
        Room::factory()->create(['room_type_id' => $type->id, 'number' => '101', 'type' => $type->name]);
        Room::factory()->create(['room_type_id' => $type->id, 'number' => '102', 'type' => $type->name]);

        $this->get(route('our.room'))->assertOk()->assertSee('Deluxe King')->assertDontSee('Room 101')->assertDontSee('Room 102');
        $this->get(route('rooms.show.public', $type))->assertOk()->assertSee('King Bed');
    }

    public function test_guest_can_save_preferences(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch(route('preferences.update'), [
            'bed_type' => 'king', 'smoking' => 'non_smoking', 'floor' => 'high', 'dietary' => 'Vegetarian',
            'airport_transfer' => 1, 'contact_method' => 'whatsapp', 'accessibility_notes' => 'Near lift',
        ])->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('user_preferences', ['user_id' => $user->id, 'bed_type' => 'king', 'airport_transfer' => 1]);
    }

    public function test_business_inquiry_is_persisted_and_admin_can_update_status(): void
    {
        $this->post(route('business.inquiries.store'), [
            'company_name' => 'Acme Group', 'contact_person' => 'Clara', 'business_email' => 'clara@acme.test', 'phone' => '0812',
            'request_type' => 'group_booking', 'guests' => 20, 'rooms' => 10, 'check_in' => now()->addMonth()->toDateString(),
            'check_out' => now()->addMonth()->addDays(2)->toDateString(), 'message' => 'Team retreat',
        ])->assertSessionHasNoErrors();
        $inquiry = BusinessInquiry::sole();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->patch(route('admin.business.update', $inquiry), ['status' => 'contacted'])->assertSessionHasNoErrors();
        $this->assertSame('contacted', $inquiry->refresh()->status);
    }

    public function test_guest_request_requires_ownership_and_uses_membership_priority(): void
    {
        $user = User::factory()->vvip()->create();
        $other = User::factory()->create();
        $room = Room::factory()->create();
        $reservation = $this->reservation($user, $room);

        $this->actingAs($other)->post(route('guest-requests.store'), ['reservation_id' => $reservation->id, 'request_type' => 'extra_towels'])->assertNotFound();
        $this->actingAs($user)->post(route('guest-requests.store'), ['reservation_id' => $reservation->id, 'request_type' => 'extra_towels', 'details' => 'Two sets'])->assertSessionHasNoErrors();

        $request = GuestServiceRequest::sole();
        $job = PriorityQueue::sole();
        $this->assertSame($request->id, $job->payload['guest_service_request_id']);
        $this->assertSame(1, $job->base_priority);
        $this->assertSame(ProcessGuestServiceRequest::class, $job->job_class);
    }

    public function test_admin_hotel_kpis_use_room_nights_and_safe_division(): void
    {
        $this->travelTo('2026-08-15 12:00:00');
        $admin = User::factory()->admin()->create();
        $guest = User::factory()->create();
        $room = Room::factory()->create(['price' => 100000]);
        Room::factory()->create();
        Reservation::create(['user_id' => $guest->id, 'room_id' => $room->id, 'name' => $guest->name, 'email' => $guest->email, 'phone' => '0812', 'check_in' => '2026-08-10', 'check_out' => '2026-08-12', 'total_price' => 200000, 'status' => 'confirmed']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('occupancyRate', 3.2)
            ->assertViewHas('adr', 100000.0)
            ->assertViewHas('averageStay', 2.0)
            ->assertViewHas('cancellationRate', 0.0);
    }

    private function reservation(User $user, Room $room): Reservation
    {
        return Reservation::create(['user_id' => $user->id, 'room_id' => $room->id, 'name' => $user->name, 'email' => $user->email, 'phone' => '0812', 'check_in' => now()->addDay()->toDateString(), 'check_out' => now()->addDays(2)->toDateString(), 'total_price' => $room->price, 'status' => 'confirmed']);
    }
}
