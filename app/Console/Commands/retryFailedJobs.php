<?php

namespace App\Console\Commands;

use App\Models\PriorityQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class retryFailedJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retry:failed-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'retry all failed jobs to the priority queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //get failed jobs
        $failedjobs = DB::table('failed_jobs')->get();
        Log::channel('watchdog')->info('retrying failed job count: ' . $failedjobs->count());

        $retriedcount = 0;

        foreach ($failedjobs as $job) {
            Log::channel('watchdog')->info("found failed job id: $job->id");

            $payload = json_decode($job->payload, true);
            if(!$payload){
                Log::channel('watchdog')->warning("payload is not json");
                continue;
            }

            $job_class =$payload['job_class'] ?? null;
            $priority = $payload['priority'] ?? 3;

            $innerPayload = $payload['payload'] ?? [];
            $reservation_id = $innerPayload['reservation_id'] ?? null;


            if(!$reservation_id || !$job_class){
                Log::channel('watchdog')->warning("reservation_id in payload for job id: $job->id");    
                    continue;
                }
                PriorityQueue::create([
                'id' => (string) Str::uuid(),
                'job_class' => $job_class,
                'payload' => json_encode(['reservation_id' => $reservation_id]),
                'priority' =>$priority,
                    'beat_count' => 0,
                'cut_count' => 0,
                'fail_count' => 0,
                'was_retried' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('failed_jobs')->where('id', $job->id)->delete();
            $retriedcount++;
        }
        Log::channel('watchdog')->info("retried $retriedcount jobs to priority queue");
     //
    }
}
