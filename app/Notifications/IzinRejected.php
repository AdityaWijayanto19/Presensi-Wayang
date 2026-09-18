<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IzinRejected extends Notification
{
    use Queueable;

    public function __construct(public $izin, public $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $jenisLabel = $this->izin->jenis_izin instanceof \App\Enums\JenisIzin
            ? $this->izin->jenis_izin->label()
            : $this->izin->jenis_izin;

        return [
            'type' => 'izin_rejected',
            'izin_id' => $this->izin->id,
            'tgl_izin' => $this->izin->tgl_izin->format('Y-m-d'),
            'jenis_izin' => $jenisLabel,
            'reason' => $this->reason,
            'message' => 'Izin tanggal ' . $this->izin->tgl_izin->format('Y-m-d') . ' ditolak.' . ($this->reason ? ' Alasan: ' . $this->reason : ''),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
