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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // Unique event identification code
            $table->string('event_code', 30)->unique();

            // Event information
            $table->string('title');
            $table->text('description')->nullable();

            // Event schedule
            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Event location
            $table->string('location')->nullable();

            // Event status
            $table->string('status', 20)->default('planned')->index();

            // User who created the event
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Faster event searching
            $table->index('event_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};