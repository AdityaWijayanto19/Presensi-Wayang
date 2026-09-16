<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('tgl_lembur');
            $table->string('status', 20)->default('pending_atasan')->after('keterangan');
            $table->string('atasan_nik', 16)->nullable()->after('status');
            $table->string('atasan_status', 20)->default('pending')->after('atasan_nik');
            $table->string('admin_status', 20)->default('pending')->after('atasan_status');
            $table->text('rejected_reason')->nullable()->after('admin_status');
            $table->string('pdf_form_path')->nullable()->after('rejected_reason');
            $table->string('foto_mulai')->nullable()->after('pdf_form_path');
            $table->string('foto_selesai')->nullable()->after('foto_mulai');
            $table->timestamp('waktu_mulai')->nullable()->after('foto_selesai');
            $table->timestamp('waktu_selesai')->nullable()->after('waktu_mulai');
            $table->integer('durasi_menit')->default(0)->after('waktu_selesai');
            $table->text('laporan_deskripsi')->nullable()->after('durasi_menit');
            $table->string('laporan_file')->nullable()->after('laporan_deskripsi');
            $table->text('laporan_images')->nullable()->after('laporan_file');
            $table->string('laporan_atasan_nik', 16)->nullable()->after('laporan_images');
            $table->string('laporan_status', 20)->nullable()->after('laporan_atasan_nik');
            $table->string('laporan_atasan_status', 20)->nullable()->after('laporan_status');
            $table->string('laporan_admin_status', 20)->nullable()->after('laporan_atasan_status');
            $table->text('laporan_rejected_reason')->nullable()->after('laporan_admin_status');
            $table->timestamp('laporan_approved_at')->nullable()->after('laporan_rejected_reason');
            $table->timestamp('approved_at')->nullable()->after('laporan_approved_at');
        });

        // Drop old columns
        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropColumn(['durasi', 'file_form', 'file_laporan']);
        });

        // Recreate index as unique
        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropIndex(['tgl_lembur', 'nik']);
            $table->unique(['nik', 'tgl_lembur']);
        });
    }

    public function down(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropUnique(['nik', 'tgl_lembur']);
            $table->index(['tgl_lembur', 'nik']);

            $table->dropColumn([
                'keterangan', 'status', 'atasan_nik', 'atasan_status', 'admin_status',
                'rejected_reason', 'pdf_form_path', 'foto_mulai', 'foto_selesai',
                'waktu_mulai', 'waktu_selesai', 'durasi_menit',
                'laporan_deskripsi', 'laporan_file', 'laporan_images',
                'laporan_atasan_nik', 'laporan_status', 'laporan_atasan_status',
                'laporan_admin_status', 'laporan_rejected_reason',
                'laporan_approved_at', 'approved_at',
            ]);
        });

        Schema::table('lemburs', function (Blueprint $table) {
            $table->string('durasi');
            $table->string('file_form');
            $table->string('file_laporan');
        });
    }
};
