<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;
use Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("users")->insert([
        //admin
        [
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin'
        ],

        //user
        [
            
                'name' => 'User',
                'username' => 'user',
                'email' => 'user@mail.com',
                'password' => Hash::make('12345678'),
                'role' => 'user'
            
        ],

        ]);
        //
    }
}
