<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unitperusahaan extends Model
{
    protected $table = 'unitperusahaans';

    public $timestamps = false;

    protected $fillable = [
        'unit',
        'perusahaan',
        'jam_masuk',
        'radius_meter',
    ];

    protected $casts = [
        'jam_masuk' => 'string',
        'radius_meter' => 'integer',
    ];

    public function lokasis(): HasMany
    {
        return $this->hasMany(Unitlokasi::class, 'unit_id', 'id');
    }
}
