<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CutiApproved extends Notification
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
            'type' => 'cuti_approved',
            'cuti_id' => $this->cuti->id,
            'tanggal_cuti' => $tanggal,
            'durasi_hari' => $this->cuti->durasi_hari,
            'message' => 'Pengajuan cuti ' . $this->cuti->durasi_hari . ' hari (' . $tanggal . ') telah disetujui.',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
