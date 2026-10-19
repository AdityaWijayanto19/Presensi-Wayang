<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CutiRejected extends Notification
{
    use Queueable;

    public function __construct(public $cuti) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $tanggal = collect((array) $this->cuti->tanggal_cuti)->implode(', ');

        return [
            'type' => 'cuti_rejected',
            'cuti_id' => $this->cuti->id,
            'tanggal_cuti' => $tanggal,
            'rejected_reason' => $this->cuti->rejected_reason,
            'message' => 'Pengajuan cuti (' . $tanggal . ') ditolak: ' . ($this->cuti->rejected_reason ?: '-'),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
