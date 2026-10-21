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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();

            // Child associated with this QR code
            $table->foreignId('child_id')
                ->constrained('children')
                ->cascadeOnDelete();

            // Secure hashed QR token
            $table->string('token_hash', 64)->unique();

            // Path to generated QR image
            $table->string('qr_image_path')->nullable();

            // QR status
            $table->string('status', 20)->default('active')->index();

            // QR lifecycle information
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_scanned_at')->nullable();

            $table->timestamps();

            // A child should normally have only one active QR record
            $table->index(['child_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};