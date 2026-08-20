<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Http\Requests\UpdateReservationStatusRequest;
use App\Jobs\sendEmailJob;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\WatchdogScheduler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function AdminDashboard(Request $request)
    {
        $rooms = Room::all();

        return view('admin.index', compact('rooms'));
    }

    public function AdminProfile(Request $request)
    {
        $data['getRecord'] = User::find(Auth::user()->id);

        return view('admin.profile', $data);
    }

    public function AdminProfileUpdate(Request $request)
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = $request->user();
        $user->name = trim($validated['name']);
        $user->username = isset($validated['username']) ? trim($validated['username']) : null;
        $user->email = trim($validated['email']);
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->phone = isset($validated['phone']) ? trim($validated['phone']) : null;
        $user->save();

        return redirect()->route('admin.profile')->with('success', 'update success');
    }

    public function AdminReservation(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'cancelled', 'completed'])],
        ]);

        $reservations = Reservation::query()
            ->with(['room', 'user'])
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('room', fn ($roomQuery) => $roomQuery->where('type', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reservationIndex', compact('reservations'));

    }

    public function UpdateReservation(UpdateReservationStatusRequest $request, Reservation $reservation, WatchdogScheduler $watchdog)
    {
        $newStatus = $request->validated('status');

        if (! ReservationStatus::canTransition($reservation->status, $newStatus)) {
            throw ValidationException::withMessages([
                'status' => "Reservation cannot transition from {$reservation->status} to {$newStatus}.",
            ]);
        }

        $reservation->update(['status' => $newStatus]);

        if ($newStatus === ReservationStatus::Confirmed->value) {
            $priority = ['vvip' => 1, 'vip' => 2, 'regular' => 3][$reservation->user?->role] ?? 3;
            $watchdog->enqueue(
                sendEmailJob::class,
                ['reservation_id' => $reservation->id],
                $priority,
                "reservation:{$reservation->id}:confirmation-email",
            );
        }

        return redirect()->route('admin.reservation')->with('success', 'Reservation update success');
    }

    /**
     * Show the form for creating a new resource.
     */
}
