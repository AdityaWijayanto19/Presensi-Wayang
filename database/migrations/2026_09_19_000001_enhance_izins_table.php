<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izins', function (Blueprint $table) {
            $table->string('keterangan')->nullable()->after('jenis_izin');
            $table->string('bukti_file')->nullable()->after('keterangan');
            $table->string('status', 20)->default('pending_atasan')->after('bukti_file');
            $table->char('atasan_nik', 16)->nullable()->after('status');
            $table->string('atasan_status', 20)->default('pending')->after('atasan_nik');
            $table->string('admin_status', 20)->default('pending')->after('atasan_status');
            $table->text('rejected_reason')->nullable()->after('admin_status');
            $table->string('pdf_form_path')->nullable()->after('rejected_reason');
            $table->timestamp('approved_at')->nullable()->after('pdf_form_path');
        });
    }

    public function down(): void
    {
        Schema::table('izins', function (Blueprint $table) {
            $table->dropColumn([
                'keterangan', 'bukti_file', 'status', 'atasan_nik',
                'atasan_status', 'admin_status', 'rejected_reason',
                'pdf_form_path', 'approved_at',
            ]);
        });
    }
};
