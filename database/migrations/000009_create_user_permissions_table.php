<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('user_type', 20);
            $table->string('user_id', 20);
            $table->string('permission_name', 20);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_type', 'user_id', 'permission_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
