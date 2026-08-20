<?php

namespace App\Jobs;

use App\Models\PriorityQueue;
use App\Services\WatchdogScheduler;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use ReflectionClass;
use RuntimeException;
use Throwable;

class ExecuteWatchdogJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 600;

    public function __construct(public int $watchdogJobId) {}

    public function uniqueId(): string
    {
        return (string) $this->watchdogJobId;
    }

    public function handle(WatchdogScheduler $scheduler): void
    {
        $job = PriorityQueue::findOrFail($this->watchdogJobId);
        if ($job->status !== 'processing') {
            return;
        }

        $scheduler->markStarted($job);

        try {
            $reflection = new ReflectionClass($job->job_class);
            $arguments = [];
            foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
                $type = $parameter->getType();
                $payloadKey = $parameter->getName().'_id';
                if (! $type || $type->isBuiltin() || ! isset($job->payload[$payloadKey])) {
                    throw new RuntimeException("Cannot resolve Watchdog argument {$parameter->getName()}.");
                }
                $arguments[] = $type->getName()::findOrFail($job->payload[$payloadKey]);
            }

            app()->call([$reflection->newInstanceArgs($arguments), 'handle']);
            $scheduler->markCompleted($job);
        } catch (Throwable $exception) {
            $scheduler->markFailed($job, $exception->getMessage());
        }
    }
}
