<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->decimal('lokasi_in_akurasi', 8, 2)->nullable()->after('lokasi_in');
            $table->integer('lokasi_in_jarak')->nullable()->after('lokasi_in_akurasi');
            $table->unsignedInteger('gps_fix_in')->nullable()->after('lokasi_in_jarak');
            $table->unsignedInteger('gps_durasi_in_ms')->nullable()->after('gps_fix_in');
            $table->string('ip_in', 45)->nullable()->after('gps_durasi_in_ms');

            $table->decimal('lokasi_out_akurasi', 8, 2)->nullable()->after('lokasi_out');
            $table->integer('lokasi_out_jarak')->nullable()->after('lokasi_out_akurasi');
            $table->unsignedInteger('gps_fix_out')->nullable()->after('lokasi_out_jarak');
            $table->unsignedInteger('gps_durasi_out_ms')->nullable()->after('gps_fix_out');
            $table->string('ip_out', 45)->nullable()->after('gps_durasi_out_ms');

            $table->string('flag_manipulasi', 255)->nullable()->after('terlambat');
        });
    }

    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropColumn([
                'lokasi_in_akurasi',
                'lokasi_in_jarak',
                'gps_fix_in',
                'gps_durasi_in_ms',
                'ip_in',
                'lokasi_out_akurasi',
                'lokasi_out_jarak',
                'gps_fix_out',
                'gps_durasi_out_ms',
                'ip_out',
                'flag_manipulasi',
            ]);
        });
    }
};
