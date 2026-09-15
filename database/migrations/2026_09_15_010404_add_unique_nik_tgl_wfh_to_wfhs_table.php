<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wfhs', function (Blueprint $table) {
            $table->dropIndex(['tgl_wfh', 'nik']);
        });

        DB::statement('DELETE w1 FROM wfhs w1
            INNER JOIN wfhs w2
            ON w1.nik = w2.nik AND w1.tgl_wfh = w2.tgl_wfh AND w1.id < w2.id');

        Schema::table('wfhs', function (Blueprint $table) {
            $table->unique(['nik', 'tgl_wfh']);
        });
    }

    public function down(): void
    {
        Schema::table('wfhs', function (Blueprint $table) {
            $table->dropUnique(['nik', 'tgl_wfh']);
            $table->index(['tgl_wfh', 'nik']);
        });
    }
};
