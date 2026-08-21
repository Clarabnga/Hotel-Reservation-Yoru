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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('capacity')->default(2);
            $table->string('bed_type')->default('King Bed');
            $table->text('amenities')->nullable();
            $table->string('image')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('room_type_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        DB::table('rooms')->select('type')->distinct()->orderBy('type')->each(function (object $room): void {
            $id = DB::table('room_types')->insertGetId([
                'name' => $room->type,
                'slug' => Str::slug($room->type).'-'.Str::lower(Str::random(5)),
                'description' => 'A calm, considered room with the essentials for a restorative stay.',
                'amenities' => DB::table('rooms')->where('type', $room->type)->value('facilities'),
                'image' => DB::table('rooms')->where('type', $room->type)->whereNotNull('image')->value('image'),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('rooms')->where('type', $room->type)->update(['room_type_id' => $id]);
        });

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('bed_type')->nullable();
            $table->string('smoking')->nullable();
            $table->string('floor')->nullable();
            $table->string('dietary')->nullable();
            $table->boolean('airport_transfer')->default(false);
            $table->string('contact_method')->nullable();
            $table->text('accessibility_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('business_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('business_email');
            $table->string('phone');
            $table->string('request_type')->index();
            $table->unsignedInteger('guests')->default(1);
            $table->unsignedInteger('rooms')->default(1);
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->string('budget')->nullable();
            $table->text('message');
            $table->string('status')->default('new')->index();
            $table->timestamps();
        });

        Schema::create('guest_service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('watchdog_job_id')->nullable()->constrained('priority_queues')->nullOnDelete();
            $table->string('request_type');
            $table->text('details')->nullable();
            $table->string('status')->default('submitted')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_service_requests');
        Schema::dropIfExists('business_inquiries');
        Schema::dropIfExists('user_preferences');
        Schema::table('rooms', fn (Blueprint $table) => $table->dropConstrainedForeignId('room_type_id'));
        Schema::dropIfExists('room_types');
    }
};
