<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->char('nik', 16)->index();
            $table->text('endpoint');
            $table->string('public_key');
            $table->string('auth_token');
            $table->timestamps();

            $table->foreign('nik')
                ->references('nik')
                ->on('karyawans')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
