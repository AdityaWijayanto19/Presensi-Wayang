<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LaporanApprovedByAtasan extends Notification
{
    use Queueable;

    public function __construct(public $wfh, public $atasan) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'laporan_approved_atasan',
            'wfh_id' => $this->wfh->id,
            'tgl_wfh' => $this->wfh->tgl_wfh,
            'atasan_nama' => $this->atasan->nama_lengkap ?? '-',
            'message' => 'Laporan WFH tanggal ' . $this->wfh->tgl_wfh . ' disetujui oleh ' . ($this->atasan->nama_lengkap ?? 'Atasan') . ', menunggu persetujuan HR',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
