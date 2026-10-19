<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CutiSubmitted extends Notification
{
    use Queueable;

    public function __construct(public $cuti, public $pengaju) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'cuti_submitted',
            'cuti_id' => $this->cuti->id,
            'pengaju_nik' => $this->pengaju->nik,
            'pengaju_nama' => $this->pengaju->nama_lengkap,
            'pengaju_jabatan' => $this->pengaju->jabatan instanceof \App\Enums\Jabatan
                ? $this->pengaju->jabatan->value
                : $this->pengaju->jabatan,
            'durasi_hari' => $this->cuti->durasi_hari,
            'message' => $this->pengaju->nama_lengkap . ' mengajukan cuti tahunan ' . $this->cuti->durasi_hari . ' hari — menunggu persetujuan.',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
