<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('enable_attendance')
                ->default(false)
                ->after('status');

            $table->boolean('enable_questionnaires')
                ->default(false)
                ->after('enable_attendance');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'enable_attendance',
                'enable_questionnaires',
            ]);
        });
    }
};