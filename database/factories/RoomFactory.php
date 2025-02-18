<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
        public function definition(): array
        {
            // Pilih tipe kamar secara acak
            $type = $this->faker->randomElement([
                'Deluxe King Room',
                'Deluxe Twin Room',
                'Premier Twin Room',
                'Suite Room',
                'Premier King Room',
                'Executive Room'
            ]);
    
            // Mapping harga dan gambar berdasarkan tipe kamar
            $priceMapping = [
                'Deluxe King Room' => 785000,
                'Deluxe Twin Room' => 843000,
                'Premier Twin Room' => 1250000,
                'Suite Room' => 3450000,
                'Premier King Room' => 1350000,
                'Executive Room' => 4000000,
            ];
    
            $imagePaths = [
                'Deluxe King Room' => 'assets/images/deluxeking.jpeg',
                'Deluxe Twin Room' => 'assets/images/deluxetwin.jpeg',
                'Premier Twin Room' => 'assets/images/premiertwin.jpeg',
                'Suite Room' => 'assets/images/suite.jpeg',
                'Premier King Room' => 'assets/images/premierking.jpeg',
                'Executive Room' => 'assets/images/executive.jpeg',
            ];
    
            // Menggunakan `unique()` untuk memastikan nomor kamar selalu unik
            return [
                'number' => str_pad($this->faker->unique()->numberBetween(1, 200), 3, '0', STR_PAD_LEFT), // Nomor kamar yang unik
                'type' => $type, // Tipe kamar yang dipilih
                'price' => $priceMapping[$type] ?? 560000, // Gunakan harga berdasarkan tipe, jika tidak ada gunakan harga default
                'facilities' => $this->faker->sentence(30), // Fasilitas kamar (kalimat acak)
                'status' => $this->faker->randomElement(['available', 'booked']), // Status kamar (tersedia atau sudah dipesan)
                'image' => $imagePaths[$type] ?? 'assets/images/default.jpg', // Gambar berdasarkan tipe kamar, jika tidak ada gunakan gambar default
            ];
        }
}
