<?php

namespace Tests\Feature;

use App\Jobs\ExecuteWatchdogJob;
use App\Jobs\sendEmailJob;
use App\Mail\ReservationReceiptMail;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\WatchdogScheduler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class WatchdogExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmation_creates_exactly_one_ranked_watchdog_email_job(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->vvip()->create();
        $reservation = $this->reservationFor($customer);

        $this->actingAs($admin)->post(route('update.reservation', $reservation), ['status' => 'confirmed']);
        $this->actingAs($admin)->post(route('update.reservation', $reservation), ['status' => 'confirmed']);

        $this->assertDatabaseCount('priority_queues', 1);
        $this->assertDatabaseHas('priority_queues', [
            'job_class' => sendEmailJob::class, 'base_priority' => 1, 'current_priority' => 1,
        ]);
    }

    public function test_command_dispatches_wrapper_without_marking_job_completed(): void
    {
        Queue::fake();
        $job = app(WatchdogScheduler::class)->enqueue(FailingTask::class, [], 2, 'dispatch-test');

        $this->artisan('watchdog:dispatch')->assertSuccessful();

        Queue::assertPushed(ExecuteWatchdogJob::class, fn ($queued) => $queued->watchdogJobId === $job->id);
        $this->assertSame('processing', $job->refresh()->status);
    }

    public function test_real_email_success_completes_job_and_failure_returns_to_watchdog(): void
    {
        Mail::fake();
        $scheduler = app(WatchdogScheduler::class);
        $reservation = $this->reservationFor(User::factory()->create());
        $emailJob = $scheduler->enqueue(sendEmailJob::class, ['reservation_id' => $reservation->id], 3, 'email-success');
        $scheduler->claimNext();

        (new ExecuteWatchdogJob($emailJob->id))->handle($scheduler);

        Mail::assertSent(ReservationReceiptMail::class);
        $this->assertSame('completed', $emailJob->refresh()->status);

        $failure = $scheduler->enqueue(FailingTask::class, [], 3, 'failure');
        $scheduler->claimNext();
        (new ExecuteWatchdogJob($failure->id))->handle($scheduler);
        $this->assertSame('waiting', $failure->refresh()->status);
        $this->assertSame(1, $failure->attempt_count);
    }

    private function reservationFor(User $user): Reservation
    {
        $room = Room::factory()->create();

        return Reservation::create([
            'user_id' => $user->id, 'room_id' => $room->id, 'name' => $user->name,
            'email' => $user->email, 'phone' => '081234567890',
            'check_in' => now()->addDay()->toDateString(), 'check_out' => now()->addDays(2)->toDateString(),
            'total_price' => $room->price, 'status' => 'pending',
        ]);
    }
}

class FailingTask
{
    public function handle(): void
    {
        throw new RuntimeException('Controlled test failure.');
    }
}
