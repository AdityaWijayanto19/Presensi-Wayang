<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WfhMarkedUnpaid extends Notification
{
    use Queueable;

    public function __construct(public $wfh, public string $reason = 'belum upload laporan') {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'wfh_unpaid',
            'wfh_id' => $this->wfh->id,
            'tgl_wfh' => $this->wfh->tgl_wfh->format('Y-m-d'),
            'reason' => $this->reason,
            'message' => 'WFH tanggal ' . $this->wfh->tgl_wfh->format('Y-m-d') . ' ditandai sebagai Unpaid karena ' . $this->reason,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function sendWebPush(object $notifiable): void
    {
        app(\App\Services\Shared\WebPushService::class)->send(
            $notifiable->nik,
            'WFH Unpaid',
            'WFH tanggal ' . $this->wfh->tgl_wfh->format('Y-m-d') . ' ditandai sebagai Unpaid karena ' . $this->reason,
            '/presensi/wfh',
            'wfh-unpaid-' . $this->wfh->id
        );
    }
}
