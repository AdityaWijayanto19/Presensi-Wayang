<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'lokasi_out',
        'terlambat',
    ];

    protected $casts = [
        'tgl_presensi' => 'date',
        'terlambat' => 'integer',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    public function lembur(): HasOne
    {
        return $this->hasOne(Lembur::class, 'nik', 'nik');
    }

}
