<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    public function test_email_verification_is_not_enabled(): void
    {
        $this->get('/verify-email')->assertNotFound();
    }
}
