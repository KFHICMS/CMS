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
        Schema::create('children', function (Blueprint $table) {
            $table->id();

            // Unique KFHI child identification code
            $table->string('child_code', 30)->unique();

            // Child personal information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();

            // Education information
            $table->string('school_name')->nullable();
            $table->string('grade', 30)->nullable();

            // Location information
            $table->text('address')->nullable();
            $table->string('district', 100)->nullable();

            // Profile information
            $table->string('profile_photo')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('notes')->nullable();

            // User who registered the child
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Keep deleted child records recoverable
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};