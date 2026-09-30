<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unitlokasi extends Model
{
    protected $table = 'unit_lokasis';

    protected $fillable = [
        'unit_id',
        'nama_lokasi',
        'lat',
        'lng',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function unitperusahaan(): BelongsTo
    {
        return $this->belongsTo(Unitperusahaan::class, 'unit_id', 'id');
    }
}
