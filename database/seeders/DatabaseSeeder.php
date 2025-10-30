<?php

namespace Database\Seeders;

use App\Models\PriorityQueue;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Room;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(RoomSeeder::class);
        
        


        User::factory(30)->create();
        Room::factory(150)->create();
       

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
