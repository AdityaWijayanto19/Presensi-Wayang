<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cutis', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'pending_admin',
                'approved',
                'rejected',
            ])->default('draft')->after('nik')->index();
            $table->string('admin_status', 20)->default('pending')->after('status');
            $table->text('rejected_reason')->nullable()->after('admin_status');
            $table->string('pdf_form_path')->nullable()->after('bukti_file');
            $table->unsignedBigInteger('approved_by')->nullable()->after('pdf_form_path');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        // Semua data lama (hasil upload sebelum alur approval) dianggap final/approved.
        DB::table('cutis')->update([
            'status' => 'approved',
            'admin_status' => 'approved',
            'approved_at' => DB::raw('COALESCE(approved_at, dikirim_tanggal)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('cutis', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'admin_status',
                'rejected_reason',
                'pdf_form_path',
                'approved_by',
                'approved_at',
            ]);
        });
    }
};
