<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cutis', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->unsignedTinyInteger('durasi_hari');
            $table->json('tanggal_cuti');
            $table->string('keterangan')->nullable();
            $table->string('bukti_file')->nullable();
            $table->timestamp('dikirim_tanggal');
            $table->timestamps();

            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->onDelete('cascade');

            $table->index('nik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cutis');
    }
};
