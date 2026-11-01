<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_response_edits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')
                ->constrained('questionnaire_responses')
                ->restrictOnDelete();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->json('changes');
            $table->timestamps();
            $table->index(['response_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_response_edits');
    }
};
