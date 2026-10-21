<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_activity_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')
                ->constrained('field_activity_submissions')
                ->restrictOnDelete();
            $table->foreignId('corrected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('reason');
            $table->json('changes');
            $table->timestamps();
            $table->index(['submission_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_activity_corrections');
    }
};