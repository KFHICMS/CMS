<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Public submissions are about new children, so child_id must allow NULL.
        // The existing unique (questionnaire_id, child_id) index still applies to
        // real children; NULLs are never treated as equal, so it stays valid.
        $hasChildForeign = collect(Schema::getForeignKeys('questionnaire_responses'))
            ->contains(fn (array $foreignKey) => $foreignKey['columns'] === ['child_id']);

        if ($hasChildForeign) {
            Schema::table('questionnaire_responses', function (Blueprint $table) {
                $table->dropForeign(['child_id']);
            });
        }

        Schema::table('questionnaire_responses', function (Blueprint $table) {
            $table->unsignedBigInteger('child_id')->nullable()->change();
        });

        Schema::table('questionnaire_responses', function (Blueprint $table) {
            $table->foreign('child_id')->references('id')->on('children')->restrictOnDelete();

            $table->foreignId('invitation_id')
                ->nullable()
                ->constrained('enumerator_invitations')
                ->nullOnDelete();
            $table->string('respondent_name', 100)->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();

            // Existing rows keep "approved"; public submissions are set to "pending".
            $table->string('review_status', 20)->default('approved')->index();
        });
    }

    public function down(): void
    {
        Schema::table('questionnaire_responses', function (Blueprint $table) {
            $table->dropForeign(['invitation_id']);
            $table->dropUnique(['idempotency_key']);
            $table->dropIndex(['review_status']);
            $table->dropColumn(['invitation_id', 'respondent_name', 'idempotency_key', 'review_status']);
        });

        // child_id is left nullable on purpose: public rows may exist and
        // cannot be forced back to NOT NULL.
    }
};