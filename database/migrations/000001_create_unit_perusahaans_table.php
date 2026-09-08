<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unitperusahaans', function (Blueprint $table) {
            $table->string('unit')->primary();
            $table->string('perusahaan');
            $table->time('jam_masuk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unitperusahaans');
    }
};
