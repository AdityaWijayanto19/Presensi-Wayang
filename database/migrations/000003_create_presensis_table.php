<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->date('tgl_presensi');
            $table->time('jam_in');
            $table->time('jam_out')->nullable();
            $table->string('foto_in');
            $table->string('foto_out')->nullable();
            $table->string('lokasi_in');
            $table->string('lokasi_out')->nullable();
            $table->integer('terlambat')->default(0);
            $table->timestamps();

            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->onDelete('cascade');

            $table->index(['tgl_presensi', 'nik']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
