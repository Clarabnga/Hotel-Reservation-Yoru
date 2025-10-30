<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('priority_queues', function (Blueprint $table) {
            $table->id();
            $table->string('priority');
            $table->json('payload');
            $table->string('job_class');
            $table->unsignedInteger('beat_count')->default(0);
            $table->unsignedInteger('cut_count')->default(0);
            $table->unsignedInteger('fail_count')->default(0);
            $table->boolean('was_retried')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priority_queues');
    }
};
