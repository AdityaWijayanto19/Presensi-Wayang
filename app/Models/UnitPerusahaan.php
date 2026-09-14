<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unitperusahaan extends Model
{

    protected $table = 'unitperusahaans';
    public $timestamps = false;

    protected $fillable = [
        'unit',
        'perusahaan',
        'jam_masuk',
    ];

    protected $casts = [
        'jam_masuk' => 'string',
    ];

}
