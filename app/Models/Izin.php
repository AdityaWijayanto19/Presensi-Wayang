<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Izin extends Model
{
    use HasFactory;
    protected $table = 'izins';
    protected $fillable = [
        'nik',
        'tgl_izin',
        'jenis_izin',
        'file',
        'dikirim_tanggal',
    ];

    protected $casts = [
        'tgl_izin' => 'date',
        'dikirim_tanggal' => 'datetime',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }
}
