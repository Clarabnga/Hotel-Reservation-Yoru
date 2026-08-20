<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });

        DB::table('reservations')
            ->whereNull('user_id')
            ->orderBy('id')
            ->each(function (object $reservation): void {
                $userId = DB::table('users')
                    ->where('email', $reservation->email)
                    ->value('id');

                if ($userId !== null) {
                    DB::table('reservations')
                        ->where('id', $reservation->id)
                        ->update(['user_id' => $userId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
