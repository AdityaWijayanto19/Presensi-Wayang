<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wfhs', function (Blueprint $table) {
            $table->dropIndex(['tgl_wfh', 'nik']);
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
