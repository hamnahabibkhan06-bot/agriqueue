<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('location');
            $t->decimal('daily_capacity', 10, 2)->default(200);   // tons per day
            $t->decimal('slot_capacity', 10, 2)->default(40);     // tons per hourly slot
            $t->unsignedTinyInteger('weighing_stations')->default(2);
            $t->unsignedTinyInteger('unloading_points')->default(3);
            $t->unsignedTinyInteger('inspection_counters')->default(2);
            $t->decimal('storage_capacity', 10, 2)->default(2000);
            $t->unsignedTinyInteger('staff_available')->default(6);
            $t->unsignedSmallInteger('avg_minutes_per_vehicle')->default(18);
            $t->string('image')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('crops', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->decimal('price_per_ton', 12, 2);
            $t->string('image')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::table('users', function (Blueprint $t) {
            $t->string('role', 20)->default('farmer')->after('email')->index(); // farmer|staff|inspector|admin
            $t->string('phone', 30)->nullable();
            $t->string('location')->nullable();
            $t->string('identification', 30)->nullable();
            $t->string('registration_status', 20)->default('verified'); // verified|suspended
            $t->foreignId('center_id')->nullable()->constrained('centers')->nullOnDelete();
        });

        Schema::create('bookings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('center_id')->constrained('centers')->cascadeOnDelete();
            $t->foreignId('crop_id')->constrained('crops')->cascadeOnDelete();
            $t->decimal('estimated_quantity', 10, 2);              // tons
            $t->string('vehicle_number', 20)->nullable();
            $t->date('booking_date');
            $t->string('time_slot', 20);
            $t->string('token_number', 30)->unique();
            $t->string('queue_status', 20)->default('booked')->index();
            $t->timestamp('checked_in_at')->nullable();
            $t->timestamp('queued_at')->nullable();
            $t->timestamps();
            $t->index(['center_id', 'booking_date']);
        });

        Schema::create('procurements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $t->decimal('gross_weight', 10, 2)->nullable();        // kg
            $t->decimal('empty_weight', 10, 2)->nullable();        // kg
            $t->decimal('actual_weight', 10, 2)->nullable();       // kg net
            $t->string('weighing_station', 20)->nullable();
            $t->timestamp('weighed_at')->nullable();
            $t->string('quality_grade', 2)->nullable();
            $t->decimal('moisture', 5, 2)->nullable();
            $t->decimal('damaged_percent', 5, 2)->nullable();
            $t->decimal('foreign_material', 5, 2)->nullable();
            $t->text('inspector_remarks')->nullable();
            $t->string('quality_status', 20)->nullable();          // accepted|rejected
            $t->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $t->decimal('price_per_unit', 12, 2)->nullable();      // per ton
            $t->decimal('total_amount', 14, 2)->nullable();
            $t->string('payment_status', 20)->default('pending');  // pending|paid
            $t->string('receipt_number', 30)->nullable()->unique();
            $t->timestamp('completion_time')->nullable();
            $t->timestamps();
        });

        Schema::create('app_notices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('message');
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action', 60);
            $t->string('details')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('app_notices');
        Schema::dropIfExists('procurements');
        Schema::dropIfExists('bookings');
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('center_id');
            $t->dropColumn(['role', 'phone', 'location', 'identification', 'registration_status']);
        });
        Schema::dropIfExists('crops');
        Schema::dropIfExists('centers');
    }
};
