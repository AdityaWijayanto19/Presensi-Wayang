<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LaporanLemburSubmitted extends Notification
{
    use Queueable;

    public function __construct(public $lembur, public $pengaju) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'laporan_lembur_submitted',
            'lembur_id' => $this->lembur->id,
            'pengaju_nik' => $this->pengaju->nik,
            'pengaju_nama' => $this->pengaju->nama_lengkap,
            'pengaju_jabatan' => $this->pengaju->jabatan instanceof \App\Enums\Jabatan ? $this->pengaju->jabatan->value : $this->pengaju->jabatan,
            'tgl_lembur' => $this->lembur->tgl_lembur->format('Y-m-d'),
            'message' => $this->pengaju->nama_lengkap . ' mengajukan laporan lembur',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
