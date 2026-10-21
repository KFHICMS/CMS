<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->string('gn_division', 150)
                ->nullable()
                ->after('district');
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn('gn_division');
        });
    }
};