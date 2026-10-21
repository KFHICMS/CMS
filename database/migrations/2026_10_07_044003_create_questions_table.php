<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('questionnaire_id')
                ->constrained('questionnaires')
                ->cascadeOnDelete();

            $table->text('question_text');

            $table->string('question_type', 30);

            $table->boolean('is_required')
                ->default(false);

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->json('settings')
                ->nullable();

            $table->timestamps();

            $table->index([
                'questionnaire_id',
                'sort_order',
            ]);

            $table->index([
                'questionnaire_id',
                'question_type',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};