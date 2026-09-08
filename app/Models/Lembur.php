<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lembur extends Model
{
    use HasFactory;
    protected $table = 'lemburs';
    protected $fillable = [
        'nik',
        'tgl_lembur',
        'durasi',
        'file_form',
        'file_laporan',
        'dikirim_tanggal',
    ];

    protected $casts = [
        'tgl_lembur' => 'date',
        'dikirim_tanggal' => 'datetime',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }
}
