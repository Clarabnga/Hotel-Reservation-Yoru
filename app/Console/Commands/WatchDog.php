<?php

namespace App\Console\Commands;

use App\Models\PriorityQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WatchDog extends Command
{
    protected $signature = 'watchdog:dispatch';
    protected $description = 'Dispatch job by priority logic';

  

    public function handle()
    {
        DB::beginTransaction();

        try{
            //queue job based on priority -> ascending
        $job = PriorityQueue::where('status', 'waiting')
            ->orderBy('priority', 'asc')
            ->orderBy('cut_count', 'asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->first();

        if (!$job) {
            $this->info("No jobs in waiting status.");
            DB::commit();
            return;
        }
        $job->update(['status' => 'processing']);
        // $job->save();
        Log::channel('watchdog')->info("select job id: {$job->id} priority: {$job->priority} cut: {$job->cut_count} fail: {$job->fail_count} beat: {$job->beat_count}");
        try {
            //retrieves class name from $job and decode payload from database
            $class = $job->job_class;
            $payload = json_decode($job->payload, true);

            if (!class_exists($class)) {
                throw new \Exception("Job class $class does not exist");
            }
            
            // Try to create the job instance using the payload directly
            $reflection = new \ReflectionClass($class);
            $instance = $reflection->newInstanceArgs($this->resolveConstructorArguments($reflection, $payload));

            dispatch($instance)->onQueue('default');
            Log::channel('watchdog')->info("Dispatched job {$class} with payload", $payload);

            $job->status = 'completed';
            $job->save();
            Log::channel('watchdog')->info("success dispatch Job {$job->id}");
        }catch (\Throwable $e) {
            $this->handleJobFailure($job, $e);
        }
        DB::commit();
        }catch(\Throwable $e){
            DB::rollBack();
            Log::channel('watchdog')->error("Error dispatching job: " . $e->getMessage());
        }
         return Command::SUCCESS;
    }

    protected function handleJobFailure(PriorityQueue $job, \Throwable $e){
            $job->fail_count++;
            $job->cut_count++;
            $job->status = 'waiting';
            $job->save();

            Log::channel('watchdog')->warning("dispatct again job id {$job->id}: {$e->getMessage()} | Fail: {$job->fail_count} | cut: {$job->cut_count} " );

            //increment bet count for lower priority
            $beatAffect = PriorityQueue::where('priority', '>', $job->priority)
            ->where('status', 'waiting')
            ->increment('beat_count');
            Log::channel('watchdog')->info("increase beat count for {$beatAffect} jobs behind");

            //promote to level up priotiy
            if($job->fail_count >= 3){
                $promoted = PriorityQueue::where('beat_count', '>=', 3)
                ->where('priority', '>', $job->priority)
                ->update([
                    'priority' => DB::raw('GREATEST(priority - 1, 0)'),
                    'beat_count' => 0
                ]);
                Log::channel('watchdog')->info("promote {$promoted} jobs to level up priority");
            }
            
            //delete job AFter 5 retry
            if ($job->fail_count >= 5) {
                $this->failLaravelJob($job);
                $job->status = 'failed';
                $job->delete();
                Log::channel('watchdog')->error("job move to the failed jobs");
            }else{
                $job->save();
                Log::channel('watchdog')->info("job save with update fail and beat count");
            }
        
    }


    protected function resolveConstructorArguments(\ReflectionClass $reflection, array $payload): array
    {
        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            return [];
        }

        $args = [];

        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();

            if ($type && !$type->isBuiltin()) {
                $className = $type->getName();

                // If it's a model and payload has an ID, try to resolve it
                if (class_exists($className) && isset($payload["{$name}_id"])) {
                    $modelInstance = $className::find($payload["{$name}_id"]);
                    if (!$modelInstance) {
                        throw new \Exception("Missing or invalid {$name}_id for class {$className}");
                    }
                    $args[] = $modelInstance;
                } else {
                    throw new \Exception("Cannot resolve parameter \${$name}");
                }
            } elseif (isset($payload[$name])) {
                $args[] = $payload[$name];
            } else {
                throw new \Exception("Missing value for parameter \${$name}");
            }
        }

        return $args;
    }

    protected function failLaravelJob(PriorityQueue $job)
    {
        DB::table('failed_jobs')->insert([
            'uuid' => Str::uuid(),
            'connection' => 'default',
            'queue' => 'default',
            'payload' => json_encode([
                'job_class' => $job->job_class,
                'payload' => json_decode($job->payload, true),
                'priority' => $job->priority    
            ]),
            'exception' => 'Too many failures in custom priority queue',
            'failed_at' => now(),
        ]);
    }
}
