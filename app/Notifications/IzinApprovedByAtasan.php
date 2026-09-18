<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IzinApprovedByAtasan extends Notification
{
    use Queueable;

    public function __construct(public $izin, public $atasan) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $jenisLabel = $this->izin->jenis_izin instanceof \App\Enums\JenisIzin
            ? $this->izin->jenis_izin->label()
            : $this->izin->jenis_izin;

        return [
            'type' => 'izin_approved_atasan',
            'izin_id' => $this->izin->id,
            'tgl_izin' => $this->izin->tgl_izin->format('Y-m-d'),
            'jenis_izin' => $jenisLabel,
            'atasan_nama' => $this->atasan->nama_lengkap ?? '-',
            'message' => 'Izin tanggal ' . $this->izin->tgl_izin->format('Y-m-d') . ' disetujui oleh ' . ($this->atasan->nama_lengkap ?? 'Atasan') . ', menunggu persetujuan HR',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
