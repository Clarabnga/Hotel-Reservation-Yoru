<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $eligibleReservations = $user->reservations()
            ->whereIn('status', ['confirmed', 'completed']);
        $eligibleSpend = (int) (clone $eligibleReservations)->sum('total_price');

        return view('profile.edit', [
            'user' => $user,
            'loyaltyPoints' => intdiv($eligibleSpend, 10000),
            'eligibleSpend' => $eligibleSpend,
            'completedStays' => (clone $eligibleReservations)->where('status', 'completed')->count(),
            'upcomingStays' => $user->reservations()
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereDate('check_out', '>=', now())
                ->count(),
            'totalReservations' => $user->reservations()->count(),
            'nextReservation' => $user->reservations()->with('room')->whereIn('status', ['pending', 'confirmed'])->whereDate('check_out', '>=', now())->orderBy('check_in')->first(),
            'preference' => $user->preference()->firstOrNew(),
        ]);
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bed_type' => ['nullable', 'in:king,twin,no_preference'], 'smoking' => ['nullable', 'in:non_smoking,smoking,no_preference'],
            'floor' => ['nullable', 'in:high,low,no_preference'], 'dietary' => ['nullable', 'string', 'max:255'],
            'airport_transfer' => ['nullable', 'boolean'], 'contact_method' => ['nullable', 'in:email,phone,whatsapp'],
            'accessibility_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $request->user()->preference()->updateOrCreate([], $data + ['airport_transfer' => $request->boolean('airport_transfer')]);

        return Redirect::route('profile.edit')->with('status', 'preferences-updated');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
