<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->string('nik')->primary();
            $table->string('nama_lengkap');
            $table->enum('jabatan', ['Intern', 'Staff', 'SPV', 'Manager', 'GM', 'Direktur'])->nullable();
            $table->string('posisi')->nullable()->index();
            $table->string('role_approved', 20)->nullable();
            $table->string('atasan_nik')->nullable()->index();
            $table->string('unit');
            $table->string('no_hp');
            $table->string('foto')->nullable()->default(null);
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('unit')
                ->references('unit')
                ->on('unitperusahaans')
                ->onDelete('cascade');

            $table->foreign('atasan_nik')
                ->references('nik')
                ->on('karyawans')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
