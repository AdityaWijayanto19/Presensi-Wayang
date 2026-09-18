<?php

namespace App\Models;

use App\Enums\IzinStatus;
use App\Enums\JenisIzin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Izin extends Model
{
    use HasFactory;

    protected $table = 'izins';

    protected $fillable = [
        'nik',
        'tgl_izin',
        'jenis_izin',
        'keterangan',
        'bukti_file',
        'status',
        'atasan_nik',
        'atasan_status',
        'admin_status',
        'rejected_reason',
        'pdf_form_path',
        'approved_at',
        'dikirim_tanggal',
    ];

    protected $casts = [
        'tgl_izin' => 'date',
        'approved_at' => 'datetime',
        'dikirim_tanggal' => 'datetime',
        'status' => IzinStatus::class,
        'jenis_izin' => JenisIzin::class,
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
