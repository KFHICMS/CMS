<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('field_activity_submissions', function (Blueprint $table) {
            $table->string('identification_method', 20)
                ->default('qr')
                ->after('attendance_status');
        });
    }

    public function down(): void
    {
        Schema::table('field_activity_submissions', function (Blueprint $table) {
            $table->dropColumn('identification_method');
        });
    }
};
