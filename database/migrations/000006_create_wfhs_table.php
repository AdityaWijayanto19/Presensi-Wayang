<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wfhs', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->enum('jabatan', ['Intern', 'Staff', 'SPV', 'Manager', 'GM', 'Direktur'])->nullable();
            $table->string('posisi')->nullable();
            $table->date('tgl_wfh');
            $table->string('live_location')->nullable();
            $table->text('deskripsi_pekerjaan')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('atasan_nik', 16)->nullable()->index();
            $table->enum('status', [
                'pending_atasan',
                'pending_admin',
                'approved',
                'rejected',
                'unpaid',
            ])->default('pending_atasan')->index();
            $table->string('atasan_status', 20)->default('pending');
            $table->string('admin_status', 20)->default('pending');
            $table->text('rejected_reason')->nullable();
            $table->string('pdf_form_path')->nullable();
            $table->text('laporan_deskripsi')->nullable();
            $table->string('laporan_file')->nullable();
            $table->text('laporan_images')->nullable();
            $table->string('laporan_atasan_nik', 16)->nullable();
            $table->string('laporan_status', 20)->nullable();
            $table->string('laporan_atasan_status', 20)->nullable();
            $table->string('laporan_admin_status', 20)->nullable();
            $table->text('laporan_rejected_reason')->nullable();
            $table->timestamp('laporan_approved_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('dikirim_tanggal');
            $table->timestamps();

            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->onDelete('cascade');

            $table->foreign('atasan_nik')
                ->references('nik')
                ->on('karyawans')
                ->onDelete('set null');

            $table->foreign('laporan_atasan_nik')
                ->references('nik')
                ->on('karyawans')
                ->onDelete('set null');

            $table->index(['tgl_wfh', 'nik']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wfhs');
    }
};
