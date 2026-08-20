<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Room::insert([
            'number' => '000',
            'type' => 'Deluxe',
            'price' => 500000,
            'facilities' => 'AC, TV, Wifi',
            'status' => 'available',
        ]);

        //
    }
}
