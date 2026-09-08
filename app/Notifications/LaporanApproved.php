<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LaporanApproved extends Notification
{
    use Queueable;

    public function __construct(public $wfh) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'laporan_approved',
            'wfh_id' => $this->wfh->id,
            'tgl_wfh' => $this->wfh->tgl_wfh,
            'message' => 'Laporan WFH tanggal ' . $this->wfh->tgl_wfh . ' telah disetujui HR.',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
