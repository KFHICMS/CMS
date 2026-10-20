<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_activity_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')
                ->constrained('field_activity_submissions')
                ->cascadeOnDelete();
            $table->string('item_name');
            $table->string('category', 100)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->date('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['submission_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_activity_benefits');
    }
};
