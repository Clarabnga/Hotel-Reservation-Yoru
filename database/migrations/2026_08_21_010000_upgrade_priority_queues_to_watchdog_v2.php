<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('priority_queues', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('idempotency_key')->nullable()->unique()->after('uuid');
            $table->unsignedTinyInteger('base_priority')->default(3)->after('job_class');
            $table->unsignedTinyInteger('current_priority')->default(3)->after('base_priority');
            $table->unsignedBigInteger('queue_sequence')->nullable()->index()->after('current_priority');
            $table->unsignedInteger('attempt_count')->default(0)->after('beat_count');
            $table->unsignedInteger('max_attempts')->default(3)->after('attempt_count');
            $table->timestamp('available_at')->nullable();
            $table->timestamp('last_started_at')->nullable();
            $table->timestamp('last_finished_at')->nullable();
            $table->timestamp('last_failed_at')->nullable();
            $table->text('last_error')->nullable();
        });

        DB::table('priority_queues')->orderBy('id')->each(function (object $job): void {
            DB::table('priority_queues')->where('id', $job->id)->update([
                'uuid' => (string) Str::uuid(),
                'base_priority' => max(1, min(3, (int) $job->priority)),
                'current_priority' => max(1, min(3, (int) $job->priority)),
                'queue_sequence' => $job->id,
                'attempt_count' => $job->fail_count,
            ]);
        });

        Schema::table('priority_queues', function (Blueprint $table) {
            $table->enum('status', ['waiting', 'processing', 'retry_wait', 'completed', 'failed'])
                ->default('waiting')->change();
        });

        Schema::create('watchdog_sequences', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedBigInteger('next_value');
        });
        DB::table('watchdog_sequences')->insert([
            'id' => 1,
            'next_value' => ((int) DB::table('priority_queues')->max('queue_sequence')) + 1,
        ]);

        Schema::create('watchdog_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watchdog_job_id')->constrained('priority_queues')->cascadeOnDelete();
            $table->string('event_type', 50)->index();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchdog_events');
        Schema::dropIfExists('watchdog_sequences');
        Schema::table('priority_queues', function (Blueprint $table) {
            $table->enum('status', ['waiting', 'processing', 'completed', 'failed'])->default('waiting')->change();
            $table->dropColumn([
                'uuid', 'idempotency_key', 'base_priority', 'current_priority', 'queue_sequence',
                'attempt_count', 'max_attempts', 'available_at', 'last_started_at',
                'last_finished_at', 'last_failed_at', 'last_error',
            ]);
        });
    }
};
