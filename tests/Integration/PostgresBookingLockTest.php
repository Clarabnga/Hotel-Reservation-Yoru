<?php

namespace Tests\Integration;

use App\Models\Room;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('postgres')]
class PostgresBookingLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_room_row_cannot_be_locked_by_two_booking_transactions(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Run with DB_CONNECTION=pgsql.');
        }

        $room = Room::factory()->create(['type' => 'Lock Test']);
        $primary = DB::connection();
        Config::set('database.connections.pgsql_competitor', config('database.connections.pgsql'));
        $competitor = DB::connection('pgsql_competitor');

        $primary->beginTransaction();
        $primary->table('rooms')->where('id', $room->id)->lockForUpdate()->first();

        try {
            $competitor->beginTransaction();
            $competitor->statement("SET LOCAL lock_timeout = '250ms'");
            $this->expectException(QueryException::class);
            $competitor->table('rooms')->where('id', $room->id)->lockForUpdate()->first();
        } finally {
            if ($competitor->transactionLevel() > 0) {
                $competitor->rollBack();
            }
            $primary->rollBack();
            DB::purge('pgsql_competitor');
        }
    }
}
