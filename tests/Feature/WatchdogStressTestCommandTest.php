<?php

namespace Tests\Feature;

use App\Jobs\ExecuteWatchdogJob;
use App\Jobs\WatchdogStressTask;
use App\Models\PriorityQueue;
use App\Services\WatchdogScheduler;
use App\Services\WatchdogStressTester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class WatchdogStressTestCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_generates_repeatable_fifo_synthetic_jobs_and_creation_events(): void
    {
        $this->artisan('watchdog:stress-test', [
            '--jobs' => 20,
            '--vvip' => 20,
            '--vip' => 30,
            '--regular' => 50,
            '--fail-rate' => 25,
            '--seed' => 8675309,
        ])->expectsOutputToContain('Generated 20 synthetic Watchdog jobs')
            ->expectsOutputToContain('Intentional failure rate: 25%')
            ->assertSuccessful();

        $jobs = PriorityQueue::orderBy('queue_sequence')->get();
        $this->assertCount(20, $jobs);
        $this->assertSame(range(1, 20), $jobs->pluck('queue_sequence')->all());
        $this->assertSame(range(1, 20), $jobs->pluck('payload')->pluck('index')->all());
        $this->assertTrue($jobs->every(fn ($job) => $job->job_class === WatchdogStressTask::class));
        $this->assertTrue($jobs->every(fn ($job) => $job->events()->where('event_type', 'JOB_CREATED')->exists()));

        $firstRun = $jobs->map(fn ($job) => [$job->base_priority, $job->payload['should_fail']])->all();
        $this->artisan('watchdog:stress-test', [
            '--jobs' => 20, '--vvip' => 20, '--vip' => 30, '--regular' => 50,
            '--fail-rate' => 25, '--seed' => 8675309,
        ])->assertSuccessful();
        $secondRun = PriorityQueue::where('queue_sequence', '>', 20)->orderBy('queue_sequence')->get()
            ->map(fn ($job) => [$job->base_priority, $job->payload['should_fail']])->all();

        $this->assertSame($firstRun, $secondRun);
    }

    public function test_fake_handler_records_normal_success_and_failure_events(): void
    {
        $this->artisan('watchdog:stress-test', [
            '--jobs' => 2, '--vvip' => 0, '--vip' => 0, '--regular' => 1,
            '--fail-rate' => 100, '--seed' => 1,
        ])->assertSuccessful();

        $scheduler = app(WatchdogScheduler::class);
        $failed = $scheduler->claimNext();
        (new ExecuteWatchdogJob($failed->id))->handle($scheduler);

        $this->assertSame('retry_wait', $failed->refresh()->status);
        $this->assertSame(
            ['JOB_CREATED', 'DISPATCHED', 'STARTED', 'FAILED', 'RETRY_WAIT'],
            $failed->events()->orderBy('id')->pluck('event_type')->all(),
        );

        $successful = app(WatchdogStressTester::class)->generate(1, ['vvip' => 1, 'vip' => 0, 'regular' => 0], 0, 1)->first();
        $scheduler->claimNext();
        (new ExecuteWatchdogJob($successful->id))->handle($scheduler);
        $this->assertSame('completed', $successful->refresh()->status);
        $this->assertSame(
            ['JOB_CREATED', 'DISPATCHED', 'STARTED', 'COMPLETED'],
            $successful->events()->orderBy('id')->pluck('event_type')->all(),
        );
    }

    public function test_invalid_options_are_rejected_without_creating_jobs(): void
    {
        $this->artisan('watchdog:stress-test', ['--jobs' => 0])->assertFailed();
        $this->artisan('watchdog:stress-test', ['--fail-rate' => 101])->assertFailed();
        $this->assertDatabaseCount('priority_queues', 0);
    }

    public function test_generator_has_a_second_production_environment_guard(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(InvalidArgumentException::class);
        app(WatchdogStressTester::class)->generate(1, ['vvip' => 1, 'vip' => 0, 'regular' => 0], 0, 1);
    }
}
