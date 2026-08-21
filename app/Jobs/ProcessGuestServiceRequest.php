<?php

namespace App\Jobs;

use App\Contracts\HandlesWatchdogPayload;
use App\Models\GuestServiceRequest;

class ProcessGuestServiceRequest implements HandlesWatchdogPayload
{
    public function handle(array $payload): void
    {
        GuestServiceRequest::whereKey($payload['guest_service_request_id'])
            ->where('status', 'submitted')->update(['status' => 'processing']);
    }
}
