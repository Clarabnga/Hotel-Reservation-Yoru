<?php

namespace App\Services;

use App\Models\PriorityQueue;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WatchdogScheduler
{
    public const BEAT_THRESHOLD = 3;

    public function enqueue(
        string $jobClass,
        array $payload,
        int $basePriority,
        string $idempotencyKey,
        int $maxAttempts = 3,
    ): PriorityQueue {
        if (! in_array($basePriority, [1, 2, 3], true)) {
            throw new InvalidArgumentException('Priority must be between 1 and 3.');
        }

        return DB::transaction(function () use ($jobClass, $payload, $basePriority, $idempotencyKey, $maxAttempts) {
            $existing = PriorityQueue::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $job = PriorityQueue::create([
                'uuid' => (string) Str::uuid(),
                'idempotency_key' => $idempotencyKey,
                'job_class' => $jobClass,
                'payload' => $payload,
                'priority' => $basePriority,
                'base_priority' => $basePriority,
                'current_priority' => $basePriority,
                'queue_sequence' => $this->nextSequence(),
                'max_attempts' => $maxAttempts,
                'status' => 'waiting',
            ]);
            $this->event($job, 'JOB_CREATED');

            return $job;
        });
    }

    public function claimNext(): ?PriorityQueue
    {
        return DB::transaction(function (): ?PriorityQueue {
            $this->reviveDueJobs();
            $job = PriorityQueue::where('status', 'waiting')
                ->where(fn ($query) => $query->whereNull('available_at')->orWhere('available_at', '<=', now()))
                ->orderBy('current_priority')->orderBy('queue_sequence')
                ->lockForUpdate()->first();

            if (! $job) {
                return null;
            }

            $this->applyBeatsBehind($job);
            $job->update(['status' => 'processing']);
            $this->event($job, 'DISPATCHED');

            return $job->refresh();
        });
    }

    public function markStarted(PriorityQueue $job): void
    {
        $job->update(['last_started_at' => now()]);
        $this->event($job, 'STARTED');
    }

    public function markCompleted(PriorityQueue $job): void
    {
        $job->update(['status' => 'completed', 'last_finished_at' => now(), 'last_error' => null]);
        $this->event($job, 'COMPLETED');
    }

    public function markFailed(PriorityQueue $job, string $error): void
    {
        DB::transaction(function () use ($job, $error): void {
            $job = PriorityQueue::whereKey($job->id)->lockForUpdate()->firstOrFail();
            $attempts = $job->attempt_count + 1;
            $job->fill(['attempt_count' => $attempts, 'last_failed_at' => now(), 'last_error' => $error]);
            $this->event($job, 'FAILED', ['attempt_count' => $attempts, 'error' => $error]);

            if ($attempts >= $job->max_attempts) {
                $job->fill(['status' => 'retry_wait', 'available_at' => now()->addMinutes(5)])->save();
                $this->event($job, 'RETRY_WAIT', ['available_at' => $job->available_at?->toISOString()]);

                return;
            }

            $job->fill(['status' => 'waiting', 'queue_sequence' => $this->nextSequence()])->save();
            $this->event($job, 'MOVED_TO_BACK');
        });
    }

    public function reviveDueJobs(?CarbonInterface $at = null): int
    {
        $at ??= now();
        $jobs = PriorityQueue::where('status', 'retry_wait')->where('available_at', '<=', $at)
            ->orderBy('available_at')->lockForUpdate()->get();

        foreach ($jobs as $job) {
            $job->update([
                'status' => 'waiting', 'available_at' => null, 'attempt_count' => 0,
                'queue_sequence' => $this->nextSequence(),
            ]);
            $this->event($job, 'REVIVED');
        }

        return $jobs->count();
    }

    private function applyBeatsBehind(PriorityQueue $selected): void
    {
        $jobs = PriorityQueue::where('status', 'waiting')
            ->where('current_priority', '>', $selected->current_priority)
            ->orderBy('current_priority')->orderBy('queue_sequence')->lockForUpdate()->get();

        foreach ($jobs as $job) {
            $job->increment('beat_count');
            $job->refresh();
            $this->event($job, 'BEAT_RECEIVED', ['beat_count' => $job->beat_count]);
            if ($job->beat_count >= self::BEAT_THRESHOLD) {
                $from = $job->current_priority;
                $job->update([
                    'current_priority' => max(1, $from - 1),
                    'beat_count' => 0,
                    'queue_sequence' => $this->nextSequence(),
                ]);
                $this->event($job, 'PROMOTED', ['from' => $from, 'to' => $job->current_priority]);
            }
        }
    }

    private function nextSequence(): int
    {
        $next = (int) DB::table('watchdog_sequences')->where('id', 1)->lockForUpdate()->value('next_value');
        DB::table('watchdog_sequences')->where('id', 1)->update(['next_value' => $next + 1]);

        return $next;
    }

    private function event(PriorityQueue $job, string $type, array $metadata = []): void
    {
        $job->events()->create(['event_type' => $type, 'metadata' => $metadata ?: null]);
    }
}
