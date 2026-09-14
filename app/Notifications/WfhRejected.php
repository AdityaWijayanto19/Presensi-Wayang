<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WfhRejected extends Notification
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
            'type' => 'wfh_rejected',
            'wfh_id' => $this->wfh->id,
            'tgl_wfh' => $this->wfh->tgl_wfh->format('Y-m-d'),
            'reason' => $this->reason,
            'message' => 'WFH tanggal ' . $this->wfh->tgl_wfh->format('Y-m-d') . ' ditolak.' . ($this->reason ? ' Alasan: ' . $this->reason : ''),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
