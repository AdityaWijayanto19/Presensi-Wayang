<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sebelumnya 1 lembur per karyawan per hari (unique). Sekarang maksimal 2
        // record lembur per hari, jadi index biasa. Kuota 2/hari dijaga di LemburService.
        //
        // Catatan MySQL: index unique (nik, tgl_lembur) dipakai oleh foreign key nik,
        // jadi FK harus di-drop dulu, index diganti, lalu FK dipasang kembali
        // dengan aturan cascade yang sama seperti migration on-update-cascade.
        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropForeign(['nik']);
        });

        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropUnique(['nik', 'tgl_lembur']);
            $table->index(['nik', 'tgl_lembur']);
        });

        Schema::table('lemburs', function (Blueprint $table) {
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropForeign(['nik']);
        });

        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropIndex(['nik', 'tgl_lembur']);
            $table->unique(['nik', 'tgl_lembur']);
        });

        Schema::table('lemburs', function (Blueprint $table) {
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
