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
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();

            // Child who requires follow-up
            $table->foreignId('child_id')
                ->constrained('children')
                ->cascadeOnDelete();

            // User responsible for the follow-up
            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Follow-up schedule
            $table->date('follow_up_date')->nullable();
            $table->date('next_follow_up_date')->nullable();

            // Follow-up details
            $table->string('category', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('action_taken')->nullable();

            // Follow-up management
            $table->string('status', 20)->default('pending')->index();
            $table->string('priority', 20)->default('medium')->index();

            $table->timestamps();

            // Useful for follow-up management
            $table->index(['child_id', 'status']);
            $table->index('next_follow_up_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};