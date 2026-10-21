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
        Schema::table('questionnaire_responses', function (Blueprint $table) {
            // Remove the old composite index.
            $table->dropIndex(
                'questionnaire_responses_questionnaire_id_child_id_index'
            );

            // Child must be identified for every questionnaire response.
            $table->unsignedBigInteger('child_id')
                ->nullable(false)
                ->change();

            // Prevent the same child from answering
            // the same questionnaire more than once.
            $table->unique(
                ['questionnaire_id', 'child_id'],
                'questionnaire_child_unique'
            );

            // Restore the questionnaire relationship.
            $table->foreign('questionnaire_id')
                ->references('id')
                ->on('questionnaires')
                ->cascadeOnDelete();

            // Restore the child relationship.
            $table->foreign('child_id')
                ->references('id')
                ->on('children')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaire_responses', function (Blueprint $table) {
            $table->dropForeign([
                'questionnaire_id',
            ]);

            $table->dropForeign([
                'child_id',
            ]);

            $table->dropUnique('questionnaire_child_unique');

            $table->index([
                'questionnaire_id',
                'child_id',
            ]);

            $table->unsignedBigInteger('child_id')
                ->nullable()
                ->change();

            $table->foreign('questionnaire_id')
                ->references('id')
                ->on('questionnaires')
                ->cascadeOnDelete();

            $table->foreign('child_id')
                ->references('id')
                ->on('children')
                ->nullOnDelete();
        });
    }
};