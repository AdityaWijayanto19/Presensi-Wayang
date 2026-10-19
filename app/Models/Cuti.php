<?php

namespace App\Models;

use App\Enums\CutiStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuti extends Model
{
    use HasFactory;

    protected $table = 'cutis';

    protected $fillable = [
        'nik',
        'status',
        'admin_status',
        'durasi_hari',
        'tanggal_cuti',
        'keterangan',
        'bukti_file',
        'rejected_reason',
        'pdf_form_path',
        'approved_by',
        'approved_at',
        'dikirim_tanggal',
    ];

    protected $casts = [
        'tanggal_cuti' => 'array',
        'dikirim_tanggal' => 'datetime',
        'status' => CutiStatus::class,
        'approved_at' => 'datetime',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', CutiStatus::Approved);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CutiStatus::PendingAdmin);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', CutiStatus::Draft);
    }

    /**
     * Record yang "mengambil" hak tanggal cuti:
     * draft, menunggu persetujuan, maupun yang sudah disetujui.
     * Record ditolak (rejected) tidak memblokir tanggal.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            CutiStatus::Draft->value,
            CutiStatus::PendingAdmin->value,
            CutiStatus::Approved->value,
        ]);
    }
}
