<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Http\Requests\UpdateReservationStatusRequest;
use App\Jobs\sendEmailJob;
use App\Models\BusinessInquiry;
use App\Models\GuestServiceRequest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\WatchdogScheduler;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function AdminDashboard(Request $request)
    {
        $now = now();
        $reservations = Reservation::with('room')->get();
        $revenueStatuses = ['confirmed', 'completed'];
        $revenueThisMonth = $reservations->whereIn('status', $revenueStatuses)->filter(fn ($r) => Carbon::parse($r->check_in)->isSameMonth($now))->sum('total_price');
        $lastMonth = $now->copy()->subMonth();
        $revenueLastMonth = $reservations->whereIn('status', $revenueStatuses)->filter(fn ($r) => Carbon::parse($r->check_in)->isSameMonth($lastMonth))->sum('total_price');
        $revenueChange = $revenueLastMonth > 0 ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100 : ($revenueThisMonth > 0 ? 100 : 0);
        $activeRooms = Room::active()->count();
        $activeStays = $reservations->whereIn('status', $revenueStatuses);
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth()->addDay();
        $occupiedRoomNights = $activeStays->sum(fn ($r) => max(0, Carbon::parse($r->check_in)->max($monthStart)->diffInDays(Carbon::parse($r->check_out)->min($monthEnd), false)));
        $availableRoomNights = $activeRooms * $now->daysInMonth;
        $staysThisMonth = $activeStays->filter(fn ($r) => Carbon::parse($r->check_in)->lt($monthEnd) && Carbon::parse($r->check_out)->gt($monthStart));
        $occupancyRate = $this->percentage($occupiedRoomNights, $availableRoomNights);
        $adr = $this->divide($revenueThisMonth, $occupiedRoomNights);
        $revPar = $this->divide($revenueThisMonth, $availableRoomNights);
        $averageStay = $this->divide($activeStays->sum(fn ($r) => Carbon::parse($r->check_in)->diffInDays($r->check_out)), $activeStays->count());
        $leadTimes = $activeStays->filter->created_at->map(fn ($r) => max(0, Carbon::parse($r->created_at)->diffInDays($r->check_in, false)));
        $cancellationRate = $this->percentage($reservations->where('status', 'cancelled')->count(), $reservations->count());
        $months = collect(range(11, 0))->map(fn ($offset) => $now->copy()->subMonths($offset));
        $monthlyReservations = $months->map(fn ($month) => $reservations->filter(fn ($r) => Carbon::parse($r->created_at)->isSameMonth($month))->count());
        $monthlyRevenue = $months->map(fn ($month) => $reservations->whereIn('status', $revenueStatuses)->filter(fn ($r) => Carbon::parse($r->check_in)->isSameMonth($month))->sum('total_price'));
        $statusDistribution = collect(['pending', 'confirmed', 'completed', 'cancelled'])->map(fn ($status) => $reservations->where('status', $status)->count());
        $membershipDistribution = collect(['regular', 'vip', 'vvip'])->map(fn ($role) => User::where('role', $role)->count());

        return view('admin.operations', [
            'totalReservations' => $reservations->count(), 'reservationsThisMonth' => $reservations->filter(fn ($r) => Carbon::parse($r->created_at)->isSameMonth($now))->count(),
            'totalUsers' => User::where('role', '!=', 'admin')->count(), 'activeRooms' => $activeRooms,
            'occupancyRate' => $occupancyRate, 'adr' => $adr, 'revPar' => $revPar, 'averageStay' => $averageStay,
            'bookingLeadTime' => $leadTimes->isEmpty() ? 0 : round($leadTimes->average(), 1), 'cancellationRate' => $cancellationRate,
            'activeGuests' => $staysThisMonth->pluck('user_id')->filter()->unique()->count(),
            'revenueThisMonth' => $revenueThisMonth, 'revenueLastMonth' => $revenueLastMonth, 'revenueChange' => $revenueChange,
            'months' => $months->map->format('M Y'), 'monthlyReservations' => $monthlyReservations, 'monthlyRevenue' => $monthlyRevenue,
            'statusDistribution' => $statusDistribution, 'membershipDistribution' => $membershipDistribution,
            'recentReservations' => Reservation::with(['room', 'user'])->latest()->limit(6)->get(),
            'upcomingCheckins' => Reservation::with(['room', 'user'])->whereIn('status', ['pending', 'confirmed'])->whereDate('check_in', '>=', $now)->orderBy('check_in')->limit(6)->get(),
            'recentUsers' => User::where('role', '!=', 'admin')->latest()->limit(5)->get(),
            'roomsAttention' => Room::whereIn('operational_status', ['inactive', 'maintenance'])->get(),
        ]);
    }

    private function divide(float|int $value, float|int $denominator): float
    {
        return $denominator > 0 ? round($value / $denominator, 1) : 0;
    }

    private function percentage(float|int $value, float|int $denominator): float
    {
        return $this->divide($value * 100, $denominator);
    }

    public function users(Request $request)
    {
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'role' => ['nullable', Rule::in(['regular', 'vip', 'vvip', 'admin'])]]);
        $users = User::withCount('reservations')->withSum(['reservations as lifetime_spend' => fn ($q) => $q->whereIn('status', ['confirmed', 'completed'])], 'total_price')
            ->when(! isset($validated['role']), fn ($q) => $q->where('role', '!=', 'admin'))
            ->when($validated['role'] ?? null, fn ($q, $role) => $q->where('role', $role))
            ->when($validated['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function showUser(User $user)
    {
        $user->load(['preference', 'guestServiceRequests.reservation.room', 'reservations' => fn ($q) => $q->with('room')->latest()])->loadCount('reservations');
        $lifetimeSpend = $user->reservations->whereIn('status', ['confirmed', 'completed'])->sum('total_price');

        return view('admin.users.show', compact('user', 'lifetimeSpend'));
    }

    public function updateMembership(Request $request, User $user)
    {
        abort_if($user->role === 'admin', 403);
        $validated = $request->validate(['role' => ['required', Rule::in(['regular', 'vip', 'vvip'])]]);
        $user->update($validated);

        return back()->with('success', 'Guest membership updated.');
    }

    public function businessInquiries(Request $request)
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::in(['new', 'contacted', 'proposal', 'confirmed', 'closed'])]]);
        $inquiries = BusinessInquiry::query()->when($data['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when($data['search'] ?? null, fn ($q, $v) => $q->where(fn ($q) => $q->where('company_name', 'like', "%{$v}%")->orWhere('contact_person', 'like', "%{$v}%")->orWhere('business_email', 'like', "%{$v}%")))->latest()->paginate(15)->withQueryString();

        return view('admin.business.index', compact('inquiries'));
    }

    public function showBusinessInquiry(BusinessInquiry $businessInquiry)
    {
        return view('admin.business.show', compact('businessInquiry'));
    }

    public function updateBusinessInquiry(Request $request, BusinessInquiry $businessInquiry)
    {
        $businessInquiry->update($request->validate(['status' => ['required', Rule::in(['new', 'contacted', 'proposal', 'confirmed', 'closed'])]]));

        return back()->with('success', 'Inquiry status updated.');
    }

    public function guestRequests(Request $request)
    {
        $data = $request->validate(['status' => ['nullable', Rule::in(['submitted', 'processing', 'completed', 'cancelled', 'failed'])]]);
        $requests = GuestServiceRequest::with(['user', 'reservation.room', 'watchdogJob'])->when($data['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->latest()->paginate(20)->withQueryString();

        return view('admin.guest-requests.index', compact('requests'));
    }

    public function updateGuestRequest(Request $request, GuestServiceRequest $guestServiceRequest)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['completed', 'cancelled'])]]);
        abort_unless(in_array($guestServiceRequest->status, ['submitted', 'processing'], true), 422);
        $guestServiceRequest->update($data);

        return back()->with('success', 'Guest request updated.');
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
