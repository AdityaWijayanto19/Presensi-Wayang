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
        Schema::table('lemburs', function (Blueprint $table) {
            $table->datetime('rencana_mulai')->nullable()->after('waktu_selesai');
            $table->datetime('rencana_selesai')->nullable()->after('rencana_mulai');
        });
    }

    public function down(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropColumn(['rencana_mulai', 'rencana_selesai']);
        });
    }
};
