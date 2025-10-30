<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

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
            'status' => 'available'
        ]);

        //
    }
}
