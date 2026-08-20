<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WatchdogScheduler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchdogDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_monitor_jobs_lanes_and_events(): void
    {
        $admin = User::factory()->admin()->create();
        $scheduler = app(WatchdogScheduler::class);
        $scheduler->enqueue('ExampleVvipJob', [], 1, 'dashboard-vvip');
        $scheduler->enqueue('ExampleRegularJob', [], 3, 'dashboard-regular');

        $this->actingAs($admin)
            ->get(route('admin.watchdog.index'))
            ->assertOk()
            ->assertSee('Watchdog V2')
            ->assertSee('VVIP')
            ->assertSee('Regular')
            ->assertSee('JOB_CREATED')
            ->assertSee('ExampleVvipJob');
    }

    public function test_dashboard_filters_jobs_by_status_and_priority(): void
    {
        $admin = User::factory()->admin()->create();
        $scheduler = app(WatchdogScheduler::class);
        $scheduler->enqueue('VisibleVipJob', [], 2, 'visible-vip');
        $scheduler->enqueue('HiddenRegularJob', [], 3, 'hidden-regular');

        $this->actingAs($admin)
            ->get(route('admin.watchdog.index', ['status' => 'waiting', 'priority' => 2]))
            ->assertOk()
            ->assertSee('VisibleVipJob')
            ->assertDontSee('HiddenRegularJob');
    }

    public function test_non_admins_cannot_access_watchdog_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.watchdog.index'))
            ->assertRedirect('/dashboard');
    }
}
