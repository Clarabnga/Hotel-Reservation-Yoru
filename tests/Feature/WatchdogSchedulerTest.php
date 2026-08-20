<?php

namespace Tests\Feature;

use App\Models\PriorityQueue;
use App\Services\WatchdogScheduler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WatchdogSchedulerTest extends TestCase
{
    use RefreshDatabase;

    private WatchdogScheduler $scheduler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scheduler = app(WatchdogScheduler::class);
    }

    public function test_jobs_are_claimed_by_priority_then_fifo_sequence(): void
    {
        $regular = $this->enqueue('regular', 3);
        $vvipA = $this->enqueue('vvip-a', 1);
        $vip = $this->enqueue('vip', 2);
        $vvipB = $this->enqueue('vvip-b', 1);

        $this->assertSame($vvipA->id, $this->claimAndComplete()->id);
        $this->assertSame($vvipB->id, $this->claimAndComplete()->id);
        $this->assertSame($vip->id, $this->claimAndComplete()->id);
        $this->assertSame($regular->id, $this->claimAndComplete()->id);
    }

    public function test_failed_job_rotates_to_back_of_its_lane(): void
    {
        $a = $this->enqueue('a', 1);
        $b = $this->enqueue('b', 1);
        $c = $this->enqueue('c', 1);

        $claimed = $this->scheduler->claimNext();
        $this->assertSame($a->id, $claimed->id);
        $this->scheduler->markFailed($claimed, 'temporary');

        $this->assertSame($b->id, $this->claimAndComplete()->id);
        $this->assertSame($c->id, $this->claimAndComplete()->id);
        $this->assertSame($a->id, $this->claimAndComplete()->id);
    }

    public function test_beats_promote_one_level_reset_and_preserve_base_priority(): void
    {
        $regular = $this->enqueue('regular', 3);

        foreach (range(1, 3) as $index) {
            $this->enqueue("vvip-first-{$index}", 1);
            $this->claimAndComplete();
        }

        $regular->refresh();
        $this->assertSame(3, $regular->base_priority);
        $this->assertSame(2, $regular->current_priority);
        $this->assertSame(0, $regular->beat_count);

        foreach (range(1, 3) as $index) {
            $this->enqueue("vvip-second-{$index}", 1);
            $this->claimAndComplete();
        }

        $regular->refresh();
        $this->assertSame(1, $regular->current_priority);
        $this->assertSame(0, $regular->beat_count);
        $this->assertSame(['PROMOTED', 'PROMOTED'], $regular->events()->where('event_type', 'PROMOTED')->pluck('event_type')->all());
    }

    public function test_exhausted_immediate_attempts_wait_then_revive_at_lane_back(): void
    {
        Carbon::setTestNow('2026-08-21 10:00:00');
        $job = $this->scheduler->enqueue('retry', [], 2, 'retry', 2);

        $this->scheduler->markFailed($this->scheduler->claimNext(), 'first');
        $this->scheduler->markFailed($this->scheduler->claimNext(), 'second');

        $job->refresh();
        $this->assertSame('retry_wait', $job->status);
        $this->assertSame('2026-08-21 10:05:00', $job->available_at->format('Y-m-d H:i:s'));
        $this->assertNull($this->scheduler->claimNext());

        Carbon::setTestNow('2026-08-21 10:05:00');
        $this->assertSame($job->id, $this->scheduler->claimNext()->id);
        $this->assertSame(0, $job->refresh()->attempt_count);
        $this->assertTrue($job->events()->where('event_type', 'REVIVED')->exists());
        Carbon::setTestNow();
    }

    public function test_idempotency_key_prevents_duplicate_jobs_and_completion_is_explicit(): void
    {
        $first = $this->scheduler->enqueue('mail', ['id' => 1], 2, 'reservation:1:mail');
        $duplicate = $this->scheduler->enqueue('mail', ['id' => 1], 2, 'reservation:1:mail');
        $this->assertSame($first->id, $duplicate->id);
        $this->assertDatabaseCount('priority_queues', 1);

        $claimed = $this->scheduler->claimNext();
        $this->assertSame('processing', $claimed->status);
        $this->scheduler->markStarted($claimed);
        $this->assertSame('processing', $claimed->refresh()->status);
        $this->scheduler->markCompleted($claimed);
        $this->assertSame('completed', $claimed->refresh()->status);
        $this->assertSame(
            ['JOB_CREATED', 'DISPATCHED', 'STARTED', 'COMPLETED'],
            $claimed->events()->orderBy('id')->pluck('event_type')->all(),
        );
    }

    private function enqueue(string $key, int $priority): PriorityQueue
    {
        return $this->scheduler->enqueue('TestJob', ['key' => $key], $priority, $key);
    }

    private function claimAndComplete(): PriorityQueue
    {
        $job = $this->scheduler->claimNext();
        $this->scheduler->markCompleted($job);

        return $job;
    }
}
