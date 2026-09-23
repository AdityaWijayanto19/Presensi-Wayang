<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LaporanLemburRejected extends Notification
{
    use Queueable;

    public function __construct(public $lembur, public $rejectedReason) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'laporan_lembur_rejected',
            'lembur_id' => $this->lembur->id,
            'rejected_reason' => $this->rejectedReason,
            'tgl_lembur' => $this->lembur->tgl_lembur->format('Y-m-d'),
            'url' => '/lembur/' . $this->lembur->id . '/laporan/edit',
            'message' => 'Laporan lembur ditolak: ' . $this->rejectedReason,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
