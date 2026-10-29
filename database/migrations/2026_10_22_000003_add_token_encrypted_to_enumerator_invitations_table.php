<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enumerator_invitations', function (Blueprint $table) {
            $table->text('token_encrypted')->nullable()->after('token_hint');
        });
    }

    public function down(): void
    {
        Schema::table('enumerator_invitations', function (Blueprint $table) {
            $table->dropColumn('token_encrypted');
        });
    }
};