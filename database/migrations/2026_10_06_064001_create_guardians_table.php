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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();

            // Child associated with this guardian
            $table->foreignId('child_id')
                ->constrained('children')
                ->cascadeOnDelete();

            // Guardian information
            $table->string('full_name');
            $table->string('relationship', 50)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('alternate_phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('occupation')->nullable();

            // Identifies the primary guardian
            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            // Useful for finding guardians of a specific child
            $table->index(['child_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};