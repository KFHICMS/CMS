<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('label', 100)->unique();
            $table->string('type', 20)->default('text');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('child_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_field_id')
                ->constrained('child_custom_fields')
                ->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['child_id', 'custom_field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_custom_field_values');
        Schema::dropIfExists('child_custom_fields');
    }
};
