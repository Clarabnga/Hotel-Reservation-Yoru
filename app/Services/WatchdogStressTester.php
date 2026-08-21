<?php

namespace App\Services;

use App\Jobs\WatchdogStressTask;
use App\Models\PriorityQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Random\Engine\Mt19937;
use Random\Randomizer;

class WatchdogStressTester
{
    /**
     * @return Collection<int, PriorityQueue>
     */
    public function generate(int $jobs, array $weights, float $failRate, int $seed): Collection
    {
        if (app()->environment('production')) {
            throw new InvalidArgumentException('Watchdog stress testing is disabled in production.');
        }

        if ($jobs < 1 || $jobs > 100000) {
            throw new InvalidArgumentException('Jobs must be between 1 and 100000.');
        }

        if ($failRate < 0 || $failRate > 100) {
            throw new InvalidArgumentException('Fail rate must be between 0 and 100.');
        }

        if (array_sum($weights) < 1 || collect($weights)->contains(fn ($weight) => $weight < 0)) {
            throw new InvalidArgumentException('Priority weights must be non-negative and total at least 1.');
        }

        $random = new Randomizer(new Mt19937($seed));
        $runId = Str::uuid()->toString();
        $priorities = ['vvip' => 1, 'vip' => 2, 'regular' => 3];
        $weightTotal = array_sum($weights);
        $created = collect();

        foreach (range(1, $jobs) as $index) {
            $draw = $random->getInt(1, $weightTotal);
            $cumulative = 0;
            $role = 'regular';

            foreach ($priorities as $candidate => $priority) {
                $cumulative += $weights[$candidate];
                if ($draw <= $cumulative) {
                    $role = $candidate;
                    break;
                }
            }

            $shouldFail = $random->getInt(1, 10000) <= (int) round($failRate * 100);
            $created->push(app(WatchdogScheduler::class)->enqueue(
                WatchdogStressTask::class,
                [
                    'stress_run_id' => $runId,
                    'index' => $index,
                    'role' => $role,
                    'seed' => $seed,
                    'should_fail' => $shouldFail,
                ],
                $priorities[$role],
                "stress:{$runId}:{$index}",
                1,
            ));
        }

        return $created;
    }
}
