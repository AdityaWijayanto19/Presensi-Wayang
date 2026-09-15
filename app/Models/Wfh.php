<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wfh extends Model
{
    protected $table = 'wfhs';
    protected $fillable = [
        'nik',
        'jabatan',      // hierarchy level (enum)
        'posisi',       // job title (string)
        'tgl_wfh',
        'live_location',
        'deskripsi_pekerjaan',
        'keterangan',
        'atasan_nik',
        'status',
        'atasan_status',
        'admin_status',
        'rejected_reason',
        'pdf_form_path',
        'laporan_deskripsi',
        'laporan_file',
        'laporan_images',
        'laporan_atasan_nik',
        'laporan_status',
        'laporan_atasan_status',
        'laporan_admin_status',
        'laporan_rejected_reason',
        'laporan_approved_at',
        'approved_at',
        'dikirim_tanggal',
    ];

    protected $casts = [
        'tgl_wfh' => 'date',
        'dikirim_tanggal' => 'datetime',
        'approved_at' => 'datetime',
        'laporan_approved_at' => 'datetime',
        'status' => \App\Enums\WfhStatus::class,
        'laporan_status' => \App\Enums\WfhStatus::class,
        'laporan_images' => 'array',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'atasan_nik', 'nik');
    }

}
