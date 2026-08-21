<?php

namespace App\Contracts;

interface HandlesWatchdogPayload
{
    public function handle(array $payload): void;
}
