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
        Schema::create('digital_ids', function (Blueprint $table) {
            $table->id();

            // Child associated with the digital ID
            $table->foreignId('child_id')
                ->constrained('children')
                ->cascadeOnDelete();

            // QR code associated with the digital ID
            $table->foreignId('qr_code_id')
                ->constrained('qr_codes')
                ->cascadeOnDelete();

            // Digital identification card information
            $table->string('card_number', 50)->unique();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Generated digital ID/card file
            $table->string('card_image_path')->nullable();

            // Card status
            $table->string('status', 20)->default('active')->index();

            $table->timestamps();

            // Useful for child/card lookups
            $table->index('child_id');
            $table->index('qr_code_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_ids');
    }
};