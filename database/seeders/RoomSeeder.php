<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['Deluxe King', 'deluxe-king', 2, 'King Bed', 1800000, 'Elegant Japanese-inspired comfort with a restful king bed.', 'AC, WiFi, Smart TV, Minibar', 'assets/images/deluxeking.jpeg'],
            ['Executive Twin', 'executive-twin', 2, 'Twin Beds', 2100000, 'A composed room for colleagues, friends or productive stays.', 'AC, WiFi, Work Desk, Smart TV', 'assets/images/executive.jpeg'],
            ['Yoru Suite', 'yoru-suite', 2, 'King Bed', 3600000, 'A spacious suite with separate living space and quiet details.', 'AC, WiFi, Lounge, Bathtub, Minibar', 'assets/images/suite.jpeg'],
            ['Premier King', 'premier-king', 2, 'King Bed', 2800000, 'An elevated stay with additional space and premium amenities.', 'AC, WiFi, Smart TV, Bathtub', 'assets/images/premierking.jpeg'],
        ];
        $number = 101;
        foreach ($types as [$name,$slug,$capacity,$bed,$price,$description,$amenities,$image]) {
            $type = RoomType::create(compact('name', 'slug', 'capacity', 'description', 'amenities', 'image') + ['bed_type' => $bed]);
            foreach (range(1, 3) as $_) {
                Room::create(['room_type_id' => $type->id, 'number' => (string) $number++, 'type' => $name, 'price' => $price, 'facilities' => $amenities, 'image' => $image, 'status' => 'available', 'operational_status' => 'active']);
            }
        }
    }
}
