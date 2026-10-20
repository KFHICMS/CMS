<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_activity_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')
                ->constrained('field_activity_submissions')
                ->cascadeOnDelete();
            $table->string('category', 100)->nullable();
            $table->text('note');
            $table->timestamp('observed_at');
            $table->timestamps();
            $table->index(['submission_id', 'observed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_activity_observations');
    }
};
