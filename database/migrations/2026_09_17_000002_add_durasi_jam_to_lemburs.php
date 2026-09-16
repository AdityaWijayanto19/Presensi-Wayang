<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            $table->float('durasi_jam')->default(0)->after('durasi_menit');
        });
    }

    public function down(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropColumn('durasi_jam');
        });
    }
};
