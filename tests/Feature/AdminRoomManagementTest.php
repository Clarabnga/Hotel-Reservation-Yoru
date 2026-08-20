<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminRoomManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_replace_a_safely_named_room_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('rooms.store'), [
            'number' => '901',
            'type' => 'Secure Suite',
            'price' => 1000000,
            'facilities' => 'WiFi',
            'operational_status' => 'active',
            'image' => $this->fakePng('unsafe-original.png'),
        ])->assertRedirect(route('rooms.index'));

        $room = Room::where('number', '901')->firstOrFail();
        $oldPath = str_replace('storage/', '', $room->image);
        $this->assertNotSame('rooms/unsafe-original.jpg', $oldPath);
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($admin)->put(route('rooms.update', $room), [
            'number' => '901',
            'type' => 'Secure Suite',
            'price' => 1000000,
            'facilities' => 'WiFi and breakfast',
            'operational_status' => 'maintenance',
            'image' => $this->fakePng('replacement.png'),
        ])->assertRedirect(route('rooms.index'));

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $room->refresh()->image));
    }

    public function test_soft_deleting_room_preserves_reservation_history(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $room = Room::factory()->create();
        $reservation = Reservation::create([
            'user_id' => $customer->id,
            'room_id' => $room->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => '081234567890',
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'total_price' => $room->price,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->delete(route('rooms.destroy', $room))->assertRedirect();

        $this->assertSoftDeleted($room);
        $this->assertNotNull($reservation->fresh());
        $this->assertSame($room->id, $reservation->fresh()->room->id);
    }

    private function fakePng(string $name): UploadedFile
    {
        $contents = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true,
        );

        return UploadedFile::fake()->createWithContent($name, $contents);
    }
}
