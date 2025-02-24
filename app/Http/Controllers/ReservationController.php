<?php

namespace App\Http\Controllers;

use App\Mail\ReservationReceiptMail;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Mail;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:15',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);
    
        // Cek apakah kamar yang dipilih sudah dibooking pada tanggal tersebut
        $isBooked = Reservation::where('room_id', $request->room_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('check_in', [$request->check_in, $request->check_out])
                      ->orWhereBetween('check_out', [$request->check_in, $request->check_out]);
            })
            ->exists();
    
        if ($isBooked) {
            // Cari kamar lain dengan tipe yang sama yang masih tersedia
            $roomType = Room::where('id', $request->room_id)->value('type'); // Ambil tipe kamar
            $alternativeRoom = Room::where('type', $roomType)
                ->whereNotIn('id', function ($query) use ($request) {
                    $query->select('room_id')
                          ->from('reservations')
                          ->whereBetween('check_in', [$request->check_in, $request->check_out])
                          ->orWhereBetween('check_out', [$request->check_in, $request->check_out]);
                })
                ->first();
    
            if (!$alternativeRoom) {
                return back()->with('error', 'No available rooms of this type for the selected dates.');
            }
    
            // Ganti room_id dengan kamar alternatif yang tersedia
            $request->merge(['room_id' => $alternativeRoom->id]);
        }
    
        // Ambil data kamar
        $room = Room::findOrFail($request->room_id);
    
        // Hitung total harga
        $totalDays = (strtotime($validated['check_out']) - strtotime($validated['check_in'])) / 86400;
        $totalPrice = $totalDays * $room->price;
    
        // Simpan reservasi ke database
        $reservation = Reservation::create([
            'room_id' => $room->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);
    
        // Cek apakah semua kamar dari tipe ini sudah penuh dalam bulan ini
        $fullyBookedRooms = Room::where('type', $room->type)->get()->filter(function ($room) use ($validated) {
            return $room->isRoomBookedForMonth(date('Y', strtotime($validated['check_in'])), date('m', strtotime($validated['check_in'])));
        });
    
        if ($fullyBookedRooms->count() >= Room::where('type', $room->type)->count()) {
            Room::where('type', $room->type)->update(['status' => 'booked']);
        }
    
        // Kirim email
        Mail::to($validated['email'])->send(new ReservationReceiptMail($reservation));
    
        return redirect()->route('receipt', ['id' => $reservation->id])->with('success', 'Reservation Success! Wait for Confirmation.');
    }
    

    

    /**
     * Display the specified resource.
     */
    public function show( $reservation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( $reservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $reservation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $reservation)
    {
        //
    }

    public function bookingForm($id){
        $room = Room::findOrFail($id);
        return view('reservation.booking', compact('room'));

    }

    public function showReceipt($id)
    {
        $reservation = Reservation::with('room')->find($id);
    
        if (!$reservation) {
            return redirect()->route('home')->with('error', 'Reservation not found.');
        }
    
        if (auth()->user()->email !== $reservation->email) {
            abort(403, 'Access denied');
        }
    
        return view('reservation.receipt', compact('reservation'));
    }
    

    // public function UserReceipt(){
    //     $reservation = Reservation::where('email', auth()->user()->email)->get();
    //     return view('reservation.receipt', compact('reservation'));
    // }
}
