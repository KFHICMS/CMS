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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // Child who attended
            $table->foreignId('child_id')
                ->constrained('children')
                ->cascadeOnDelete();

            // Event attended
            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            // User who marked the attendance
            $table->foreignId('marked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Attendance information
            $table->timestamp('attendance_time')->useCurrent();
            $table->string('method', 20)->default('qr');
            $table->string('status', 20)->default('present')->index();
            $table->text('remarks')->nullable();

            $table->timestamps();

            // A child should have only one attendance record per event
            $table->unique(['child_id', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};