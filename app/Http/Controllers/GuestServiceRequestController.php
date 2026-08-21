<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessGuestServiceRequest;
use App\Models\GuestServiceRequest;
use App\Models\Reservation;
use App\Services\WatchdogScheduler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GuestServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $reservations = $request->user()->reservations()->with('room')->whereIn('status', ['pending', 'confirmed'])->whereDate('check_out', '>=', now())->get();
        $requests = $request->user()->guestServiceRequests()->with(['reservation.room', 'watchdogJob'])->latest()->get();

        return view('reservation.requests', compact('reservations', 'requests'));
    }

    public function store(Request $request, WatchdogScheduler $watchdog)
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'integer'],
            'request_type' => ['required', Rule::in(['airport_transfer', 'late_checkout', 'extra_bed', 'extra_towels', 'room_decoration', 'dietary_request', 'housekeeping', 'other'])],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);
        $reservation = Reservation::whereKey($data['reservation_id'])->where('user_id', $request->user()->id)->whereIn('status', ['pending', 'confirmed'])->firstOrFail();
        DB::transaction(function () use ($data, $request, $watchdog): void {
            $serviceRequest = GuestServiceRequest::create($data + ['user_id' => $request->user()->id]);
            $priority = ['vvip' => 1, 'vip' => 2, 'regular' => 3][$request->user()->role] ?? 3;
            $job = $watchdog->enqueue(ProcessGuestServiceRequest::class, ['guest_service_request_id' => $serviceRequest->id], $priority, "guest-request:{$serviceRequest->id}");
            $serviceRequest->update(['watchdog_job_id' => $job->id]);
        });

        return back()->with('success', 'Your request has been sent to Yoru operations.');
    }
}
