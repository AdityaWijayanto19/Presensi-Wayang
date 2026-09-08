<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LaporanRejected extends Notification
{
    use Queueable;

    public function __construct(public $wfh, public $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'laporan_rejected',
            'wfh_id' => $this->wfh->id,
            'tgl_wfh' => $this->wfh->tgl_wfh,
            'reason' => $this->reason,
            'message' => 'Laporan WFH tanggal ' . $this->wfh->tgl_wfh . ' ditolak.' . ($this->reason ? ' Alasan: ' . $this->reason : ''),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
