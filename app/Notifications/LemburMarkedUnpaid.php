<?php

namespace App\Notifications;

use App\Services\Shared\WebPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LemburMarkedUnpaid extends Notification
{
    use Queueable;

    public function __construct(public $lembur, public string $reason = 'belum upload laporan') {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $tgl = is_string($this->lembur->tgl_lembur) ? $this->lembur->tgl_lembur : $this->lembur->tgl_lembur->format('Y-m-d');

        return [
            'type' => 'lembur_unpaid',
            'lembur_id' => $this->lembur->id,
            'tgl_lembur' => $tgl,
            'reason' => $this->reason,
            'message' => 'Lembur tanggal '.$tgl.' ditandai sebagai Unpaid karena '.$this->reason,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function sendWebPush(object $notifiable): void
    {
        $tgl = is_string($this->lembur->tgl_lembur) ? $this->lembur->tgl_lembur : $this->lembur->tgl_lembur->format('Y-m-d');
        app(WebPushService::class)->send(
            $notifiable->nik,
            'Lembur Unpaid',
            'Lembur tanggal '.$tgl.' ditandai sebagai Unpaid karena '.$this->reason,
            '/lembur',
            'lembur-unpaid-'.$this->lembur->id
        );
    }
}
