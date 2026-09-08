<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WfhSubmitted extends Notification
{
    use Queueable;

    public function __construct(public $wfh, public $pengaju) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'wfh_submitted',
            'wfh_id' => $this->wfh->id,
            'pengaju_nik' => $this->pengaju->nik,
            'pengaju_nama' => $this->pengaju->nama_lengkap,
            'pengaju_jabatan' => $this->pengaju->jabatan instanceof \App\Enums\Jabatan ? $this->pengaju->jabatan->value : $this->pengaju->jabatan,
            'tgl_wfh' => $this->wfh->tgl_wfh,
            'message' => $this->pengaju->nama_lengkap . ' mengajukan WFH pada tanggal ' . $this->wfh->tgl_wfh,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
