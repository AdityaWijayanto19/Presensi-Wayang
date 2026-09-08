<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lemburs', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->date('tgl_lembur');
            $table->string('durasi');
            $table->string('file_form');
            $table->string('file_laporan');
            $table->timestamp('dikirim_tanggal');
            $table->timestamps();

            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->onDelete('cascade');

            $table->index(['tgl_lembur', 'nik']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lemburs');
    }
};
