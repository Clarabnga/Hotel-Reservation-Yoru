<?php

namespace App\Console\Commands;

use App\Models\PriorityQueue;
use App\Models\Reservation;
use Illuminate\Console\Command;

class seedFakeJobs extends Command
{
    protected $signature = 'seed:fake-jobs';
    protected $description = 'Seed dummy jobs for priority queue testing';

    public function handle()
    {
        $reservations = Reservation::take(2)->get();

        // Simulate random roles per reservation
        $rolePriority = ['vvip' => 1, 'vip' => 2, 'regular' => 3];
        $roles = array_keys($rolePriority);

        foreach ($reservations as $reservation) {
            $role = $roles[array_rand($roles)]; // Random role
            $priority = $rolePriority[$role];

            PriorityQueue::create([
                'job_class' => \App\Jobs\FakeJob::class,
                'payload' => json_encode([
                    'reservation_id' => $reservation->id,
                    'priority_id' => PriorityQueue::find('priority') 
                ]),
                'priority' => $priority,
                'cut_count' => 0,
                'fail_count' => 0,
                'beat_count' => 0,
            ]);
        }

        $this->info("Seeded: " . $reservations->count() . " fake jobs.");
    }
}
