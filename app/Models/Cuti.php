<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuti extends Model
{
    use HasFactory;

    protected $table = 'cutis';

    protected $fillable = [
        'nik',
        'durasi_hari',
        'tanggal_cuti',
        'keterangan',
        'bukti_file',
        'dikirim_tanggal',
    ];

    protected $casts = [
        'tanggal_cuti' => 'array',
        'dikirim_tanggal' => 'datetime',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }
}
