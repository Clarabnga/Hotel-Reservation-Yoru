<?php

namespace App\Console\Commands;

use App\Jobs\ExecuteWatchdogJob;
use App\Services\WatchdogScheduler;
use Illuminate\Console\Command;

class WatchDog extends Command
{
    protected $signature = 'watchdog:dispatch';

    protected $description = 'Claim and dispatch the next Watchdog job';

    public function handle(WatchdogScheduler $scheduler): int
    {
        $job = $scheduler->claimNext();
        if (! $job) {
            $this->info('No Watchdog jobs are ready.');

            return self::SUCCESS;
        }

        ExecuteWatchdogJob::dispatch($job->id)->onQueue('watchdog');
        $this->info("Dispatched Watchdog job {$job->uuid}.");

        return self::SUCCESS;
    }
}
