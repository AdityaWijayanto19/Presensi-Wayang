<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unitperusahaans', function (Blueprint $table) {
            $table->unsignedInteger('radius_meter')->default(100)->after('jam_masuk');
        });

        Schema::create('unit_lokasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')
                ->constrained('unitperusahaans')
                ->cascadeOnDelete();
            $table->string('nama_lokasi', 100);
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_lokasis');

        Schema::table('unitperusahaans', function (Blueprint $table) {
            $table->dropColumn('radius_meter');
        });
    }
};
