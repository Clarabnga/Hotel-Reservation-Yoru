<?php

namespace App\Http\Controllers;

use App\Exceptions\NoAvailableRoomException;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\ReservationBookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $reservations = $request->user()
            ->reservations()
            ->with('room')
            ->latest('check_in')
            ->get();

        return view('reservation.index', [
            'upcomingReservations' => $reservations->whereIn('status', ['pending', 'confirmed'])
                ->where('check_out', '>=', now()->toDateString()),
            'pastReservations' => $reservations->whereIn('status', ['confirmed', 'completed'])
                ->where('check_out', '<', now()->toDateString()),
            'cancelledReservations' => $reservations->where('status', 'cancelled'),
        ]);
    }

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
    public function store(StoreReservationRequest $request, ReservationBookingService $bookingService)
    {
        $validated = $request->validated();
        $user = $request->user();
        $requestedRoom = Room::findOrFail($validated['room_id']);

        try {
            $reservation = $bookingService->book($user, $requestedRoom, $validated);
        } catch (NoAvailableRoomException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        // Kirim email
        return redirect()->route('receipt', $reservation)->with('success', 'Reservation Success! Wait for Confirmation.');

    }

    /**
     * Display the specified resource.
     */
    public function show($reservation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($reservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $reservation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($reservation)
    {
        //
    }

    public function bookingForm($id)
    {
        $room = Room::active()->findOrFail($id);

        return view('reservation.booking', compact('room'));

    }

    public function showReceipt(Reservation $reservation)
    {
        Gate::authorize('view', $reservation);
        $reservation->load('room');

        return view('reservation.receipt', compact('reservation'));
    }

    public function cancel(Reservation $reservation)
    {
        Gate::authorize('cancel', $reservation);

        DB::transaction(function () use ($reservation): void {
            $lockedReservation = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('cancel', $lockedReservation);
            $lockedReservation->update(['status' => 'cancelled']);
        });

        return redirect()->route('reservations.index')->with('success', 'Reservation cancelled.');
    }

    // public function UserReceipt(){
    //     $reservation = Reservation::where('email', auth()->user()->email)->get();
    //     return view('reservation.receipt', compact('reservation'));
    // }
}
