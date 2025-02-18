<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Room;



class ReservationController extends Controller
{
    public function create($roomId)
    {
        // Ambil data kamar berdasarkan ID
        $room = Room::findOrFail($roomId);

        // Tampilkan form reservasi dengan data kamar
        return view('reservations.create', compact('room'));
    }

    public function store(Request $request, $roomId)
    {
        // Validasi input form
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'guests' => 'required|integer',
            'check_in' => 'required|date',
            'check_out' => 'required|date',
        ]);

        // Simpan data reservasi
        $room = Room::findOrFail($roomId);

        // Hitung total biaya berdasarkan jumlah malam
        $checkInDate = Carbon::parse($request->check_in);
        $checkOutDate = Carbon::parse($request->check_out);
        $nights = $checkInDate->diffInDays($checkOutDate);
        $totalCost = $nights * $room->price_per_night;

        // Simpan reservasi
        Reservation::create([
            'room_id' => $room->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'guests' => $request->guests,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'total_cost' => $totalCost,
            'status' => 'waiting', // status menunggu pembayaran
        ]);

        return redirect()->route('rooms.index')->with('success', 'Reservasi berhasil dibuat!');
    }
}

