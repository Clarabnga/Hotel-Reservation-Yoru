<?php

namespace App\Jobs;

use App\Contracts\HandlesWatchdogPayload;
use RuntimeException;

class WatchdogStressTask implements HandlesWatchdogPayload
{
    public function handle(array $payload): void
    {
        if (($payload['should_fail'] ?? false) === true) {
            throw new RuntimeException("Synthetic Watchdog failure for stress job {$payload['index']}.");
        }
    }
}
