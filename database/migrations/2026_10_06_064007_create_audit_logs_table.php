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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // User who performed the action
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Action information
            $table->string('action', 50);
            $table->string('module', 50);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->text('description')->nullable();

            // Request information
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Useful for audit-log searching
            $table->index(['module', 'record_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};