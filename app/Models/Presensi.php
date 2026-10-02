<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensis';

    protected $fillable = [
        'nik',
        'tgl_presensi',
        'jam_in',
        'jam_out',
        'foto_in',
        'foto_out',
        'lokasi_in',
        'lokasi_in_akurasi',
        'lokasi_in_jarak',
        'gps_fix_in',
        'gps_durasi_in_ms',
        'ip_in',
        'lokasi_out',
        'lokasi_out_akurasi',
        'lokasi_out_jarak',
        'gps_fix_out',
        'gps_durasi_out_ms',
        'ip_out',
        'terlambat',
        'flag_manipulasi',
    ];

    protected $casts = [
        'tgl_presensi' => 'date',
        'terlambat' => 'integer',
        'lokasi_in_akurasi' => 'float',
        'lokasi_in_jarak' => 'integer',
        'gps_fix_in' => 'integer',
        'gps_durasi_in_ms' => 'integer',
        'lokasi_out_akurasi' => 'float',
        'lokasi_out_jarak' => 'integer',
        'gps_fix_out' => 'integer',
        'gps_durasi_out_ms' => 'integer',
    ];

    /**
     * Daftar kode flag manipulasi yang dikenal (disimpan koma-separated).
     */
    public const FLAG_KOORDINAT_IDENTIK = 'koordinat_identik';

    public const FLAG_TELEPORT = 'teleport';

    /**
     * Label human-readable untuk setiap flag manipulasi.
     */
    public const FLAG_LABELS = [
        self::FLAG_KOORDINAT_IDENTIK => 'Koordinat identik',
        self::FLAG_TELEPORT => 'Teleport',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    /**
     * Daftar flag manipulasi (pisah koma) untuk baris presensi ini.
     *
     * @return array<int, string>
     */
    public function flags(): array
    {
        if ($this->flag_manipulasi === null || $this->flag_manipulasi === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->flag_manipulasi))));
    }

    public function dicurigaiManipulasi(): bool
    {
        return $this->flags() !== [];
    }
}
