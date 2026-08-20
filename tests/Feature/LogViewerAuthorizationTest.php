<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogViewerAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_access_log_viewer(): void
    {
        $this->get('/log-viewer')->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/log-viewer')->assertForbidden();
    }

    public function test_admin_can_access_log_viewer(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/log-viewer')->assertOk();
    }
}
