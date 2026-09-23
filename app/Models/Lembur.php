<?php

namespace App\Models;

use App\Enums\LemburStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lembur extends Model
{
    protected $table = 'lemburs';

    protected $fillable = [
        'nik',
        'tgl_lembur',
        'keterangan',
        'status',
        'atasan_nik',
        'atasan_status',
        'admin_status',
        'rejected_reason',
        'pdf_form_path',
        'foto_mulai',
        'foto_selesai',
        'waktu_mulai',
        'waktu_selesai',
        'rencana_mulai',
        'rencana_selesai',
        'durasi_menit',
        'durasi_jam',
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
        'tgl_lembur' => 'date',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'rencana_mulai' => 'datetime',
        'rencana_selesai' => 'datetime',
        'dikirim_tanggal' => 'datetime',
        'approved_at' => 'datetime',
        'laporan_approved_at' => 'datetime',
        'durasi_menit' => 'integer',
        'durasi_jam' => 'float',
        'status' => LemburStatus::class,
        'laporan_status' => LemburStatus::class,
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

    public function getDurasiFormattedAttribute(): ?string
    {
        $jam = $this->durasi_jam ?? 0;
        if ($jam <= 0) return null;
        if ($jam > 5) return 'prorate';
        $formatted = rtrim(rtrim(number_format($jam, 1, '.', ''), '0'), '.');
        return $formatted . ' jam';
    }

    public function getRencanaWaktuAttribute(): ?string
    {
        if (!$this->rencana_mulai) return null;
        $mulai = $this->rencana_mulai instanceof \Carbon\Carbon
            ? $this->rencana_mulai->format('H:i')
            : \Carbon\Carbon::parse($this->rencana_mulai)->format('H:i');
        if (!$this->rencana_selesai) {
            return ($this->durasi_jam ?? 0) > 5 ? $mulai . ' - Menyesuaikan' : null;
        }
        $selesai = $this->rencana_selesai instanceof \Carbon\Carbon
            ? $this->rencana_selesai->format('H:i')
            : \Carbon\Carbon::parse($this->rencana_selesai)->format('H:i');
        return $mulai . ' - ' . $selesai;
    }

    public function getIsPreShiftAttribute(): bool
    {
        if (!$this->rencana_mulai) return false;
        $jamBukaPresensi = '07:00:00';
        $jamMulai = $this->rencana_mulai instanceof \Carbon\Carbon
            ? $this->rencana_mulai->format('H:i:s')
            : \Carbon\Carbon::parse($this->rencana_mulai)->format('H:i:s');
        return $jamMulai < $jamBukaPresensi;
    }
}
