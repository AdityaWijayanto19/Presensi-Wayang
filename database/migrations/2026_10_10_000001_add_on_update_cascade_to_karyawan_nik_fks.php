<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropForeign(['atasan_nik']);
            $table->foreign('atasan_nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::table('presensis', function (Blueprint $table) {
            $table->dropForeign(['nik']);
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::table('izins', function (Blueprint $table) {
            $table->dropForeign(['nik']);
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropForeign(['nik']);
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::table('wfhs', function (Blueprint $table) {
            $table->dropForeign(['nik']);
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->dropForeign(['atasan_nik']);
            $table->foreign('atasan_nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->dropForeign(['laporan_atasan_nik']);
            $table->foreign('laporan_atasan_nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::table('cutis', function (Blueprint $table) {
            $table->dropForeign(['nik']);
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['nik']);
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropForeign(['atasan_nik']);
            $table->foreign('atasan_nik')
                ->references('nik')
                ->on('karyawans')
                ->nullOnDelete();
        });

        Schema::table('presensis', function (Blueprint $table) {
            $table->dropForeign(['nik']);
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnDelete();
        });

        Schema::table('izins', function (Blueprint $table) {
            $table->dropForeign(['nik']);
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnDelete();
        });

        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropForeign(['nik']);
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnDelete();
        });

        Schema::table('wfhs', function (Blueprint $table) {
            $table->dropForeign(['nik']);
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnDelete();

            $table->dropForeign(['atasan_nik']);
            $table->foreign('atasan_nik')
                ->references('nik')
                ->on('karyawans')
                ->nullOnDelete();

            $table->dropForeign(['laporan_atasan_nik']);
            $table->foreign('laporan_atasan_nik')
                ->references('nik')
                ->on('karyawans')
                ->nullOnDelete();
        });

        Schema::table('cutis', function (Blueprint $table) {
            $table->dropForeign(['nik']);
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnDelete();
        });

        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['nik']);
            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->cascadeOnDelete();
        });
    }
};
