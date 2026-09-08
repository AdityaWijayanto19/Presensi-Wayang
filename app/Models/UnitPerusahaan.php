<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Unitperusahaan extends Model
{
    use HasFactory;

    protected $table = 'unitperusahaans';
    protected $primaryKey = 'unit';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'unit',
        'perusahaan',
        'jam_masuk',
    ];

    protected $casts = [
        'jam_masuk' => 'datetime:H:i:s',
    ];

    public function karyawans(): HasMany
    {
        return $this->hasMany(Karyawan::class, 'unit', 'unit');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'unit', 'unit');
    }
}
