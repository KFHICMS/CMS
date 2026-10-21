<?php

use App\Models\Child;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->foreignId('child_id')->nullable()->change();
            $table->nullableMorphs('target');
        });

        DB::table('follow_ups')
            ->whereNotNull('child_id')
            ->update([
                'target_type' => Child::class,
                'target_id' => DB::raw('child_id'),
            ]);
    }

    public function down(): void
    {
        if (DB::table('follow_ups')->whereNull('child_id')->exists()) {
            throw new RuntimeException(
                'Cannot roll back module follow-ups while records without a child target exist.'
            );
        }

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropMorphs('target');
            $table->foreignId('child_id')->nullable(false)->change();
        });
    }
};
