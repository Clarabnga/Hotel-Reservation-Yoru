<?php

use App\Services\WatchdogStressTester;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (app()->environment(['local', 'testing'])) {
    Artisan::command('watchdog:stress-test
        {--jobs=100 : Total synthetic jobs to generate}
        {--vvip=10 : VVIP distribution weight}
        {--vip=30 : VIP distribution weight}
        {--regular=60 : Regular distribution weight}
        {--fail-rate=10 : Percentage of handlers that intentionally fail}
        {--seed=12345 : Deterministic random seed}', function (WatchdogStressTester $tester) {
        $integerOptions = ['jobs', 'vvip', 'vip', 'regular', 'seed'];
        foreach ($integerOptions as $option) {
            if (filter_var($this->option($option), FILTER_VALIDATE_INT) === false) {
                $this->error("--{$option} must be an integer.");

                return Command::FAILURE;
            }
        }

        if (! is_numeric($this->option('fail-rate'))) {
            $this->error('--fail-rate must be numeric.');

            return Command::FAILURE;
        }

        $weights = [
            'vvip' => (int) $this->option('vvip'),
            'vip' => (int) $this->option('vip'),
            'regular' => (int) $this->option('regular'),
        ];

        try {
            $created = $tester->generate(
                (int) $this->option('jobs'),
                $weights,
                (float) $this->option('fail-rate'),
                (int) $this->option('seed'),
            );
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return Command::FAILURE;
        }

        $actual = [
            'vvip' => $created->where('base_priority', 1)->count(),
            'vip' => $created->where('base_priority', 2)->count(),
            'regular' => $created->where('base_priority', 3)->count(),
        ];
        $failures = $created->filter(fn ($job) => $job->payload['should_fail'])->count();

        $this->info("Generated {$created->count()} synthetic Watchdog jobs (seed {$this->option('seed')}).");
        $this->table(
            ['Lane', 'Weight', 'Generated'],
            collect(['vvip', 'vip', 'regular'])->map(fn ($role) => [
                strtoupper($role), $weights[$role], $actual[$role],
            ])->all(),
        );
        $this->line("Intentional failure rate: {$this->option('fail-rate')}% ({$failures} jobs). ");

        return Command::SUCCESS;
    })->purpose('Generate development-only synthetic Watchdog load');
}

Schedule::command('watchdog:dispatch')->everyMinute()->withoutOverlapping();
// Schedule::command('retry:failed-jobs')->everyThirtySeconds();
