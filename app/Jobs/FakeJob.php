<?php

namespace App\Jobs;

use App\Models\PriorityQueue;
use App\Models\Reservation;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FakeJob implements ShouldQueue
{
    use Queueable;
    public $tries = 5;
    public $backoff = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(public Reservation $reservation, public PriorityQueue $priority)
    {
        //
    }

    /**
     * Execute the job.
     */
   public function handle(): void
{
    Log::channel('watchdog')->info("running fake job for reservationr id {$this->reservation->id}");

    if (rand(1, 100) <= 70) {
        Log::channel('watchdog')->warning("fake job {$this->reservation->id} failed on purpose");
        throw new Exception("fake job {$this->reservation->id} failed on purpose");
    }

    Log::channel('watchdog')->info("fake job {$this->reservation->id} completed successfully");
}



}

