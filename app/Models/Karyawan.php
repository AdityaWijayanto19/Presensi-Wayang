<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Karyawan extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $table = 'karyawans';
    protected $primaryKey = 'nik';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nik',
        'nama_lengkap',
        'jabatan',      // hierarchy level (Intern/Staff/SPV/Manager/GM/Direktur)
        'posisi',       // job title (Web Developer, Staff Accounting, etc)
        'role_approved', // Role Approved dropdown (Staff/Manager/GM/Direktur)
        'atasan_nik',
        'unit',
        'unit_id',
        'no_hp',
        'foto',
        'jatah_cuti',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function unitperusahaan(): BelongsTo
    {
        return $this->belongsTo(Unitperusahaan::class, 'unit_id', 'id');
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(self::class, 'atasan_nik', 'nik');
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'nik', 'nik');
    }

    public function izin(): HasMany
    {
        return $this->hasMany(Izin::class, 'nik', 'nik');
    }

    public function lembur(): HasMany
    {
        return $this->hasMany(Lembur::class, 'nik', 'nik');
    }

    public function wfh(): HasMany
    {
        return $this->hasMany(Wfh::class, 'nik', 'nik');
    }

    public function cuti(): HasMany
    {
        return $this->hasMany(Cuti::class, 'nik', 'nik');
    }

    public function totalCutiTerpakai(): int
    {
        return (int) $this->cuti()->sum('durasi_hari');
    }

    public function sisaCuti(): int
    {
        return max(0, (int) $this->jatah_cuti - $this->totalCutiTerpakai());
    }
}
