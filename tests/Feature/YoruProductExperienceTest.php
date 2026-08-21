<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YoruProductExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_hotel_pages_and_branded_not_found_page_render(): void
    {
        Room::factory()->create(['type' => 'Yoru Suite']);

        foreach (['/', '/our-rooms', '/facilities', '/offers', '/about', '/access'] as $uri) {
            $this->get($uri)->assertOk();
        }

        $this->get('/a-quiet-dead-end')
            ->assertNotFound()
            ->assertSee('wandered');
    }

    public function test_availability_search_excludes_an_overlapping_room(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['type' => 'Moon Room']);
        $room->reservations()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '081234567890',
            'check_in' => '2026-09-10',
            'check_out' => '2026-09-15',
            'total_price' => $room->price,
            'status' => 'confirmed',
        ]);

        $this->get(route('our.room', ['check_in' => '2026-09-11', 'check_out' => '2026-09-13']))
            ->assertOk()
            ->assertSee('No rooms found');
    }

    public function test_admin_operations_and_user_management_pages_render(): void
    {
        $admin = User::factory()->admin()->create();
        $guest = User::factory()->vip()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Hotel operations');
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee($guest->email);
        $this->actingAs($admin)->get(route('admin.users.show', $guest))->assertOk()->assertSee('Reservation history');
    }
}
