<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_information', function (Blueprint $table) {
            $table->id();

            $table->foreignId('child_id')
                ->constrained('children')
                ->cascadeOnDelete();

            $table->string('information_type', 100);

            $table->string('title', 255);

            $table->text('description')->nullable();

            $table->date('information_date')->nullable();

            $table->foreignId('added_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_information');
    }
};