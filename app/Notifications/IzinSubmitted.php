<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IzinSubmitted extends Notification
{
    use Queueable;

    public function __construct(public $izin, public $pengaju) {}

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
            'type' => 'izin_submitted',
            'izin_id' => $this->izin->id,
            'pengaju_nik' => $this->pengaju->nik,
            'pengaju_nama' => $this->pengaju->nama_lengkap,
            'pengaju_jabatan' => $this->pengaju->jabatan instanceof \App\Enums\Jabatan
                ? $this->pengaju->jabatan->value
                : $this->pengaju->jabatan,
            'tgl_izin' => $this->izin->tgl_izin->format('Y-m-d'),
            'jenis_izin' => $jenisLabel,
            'message' => $this->pengaju->nama_lengkap . ' mengajukan izin (' . $jenisLabel . ') pada tanggal ' . $this->izin->tgl_izin->format('Y-m-d'),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
