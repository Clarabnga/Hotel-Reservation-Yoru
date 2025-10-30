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
        Schema::table('priority_queues', function (Blueprint $table) {
            $table->enum('status', ['waiting', 'processing', 'completed', 'failed'])->default('waiting')->after('was_retried');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('priority_queues', function (Blueprint $table) {
            $table->dropColumn('status');
            //
        });
    }
};
