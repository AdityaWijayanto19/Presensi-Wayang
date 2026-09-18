<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IzinApproved extends Notification
{
    use Queueable;

    public function __construct(public $izin) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $jenisLabel = $this->izin->jenis_izin instanceof \App\Enums\JenisIzin
            ? $this->izin->jenis_izin->label()
            : $this->izin->jenis_izin;

        $message = 'Izin tanggal ' . $this->izin->tgl_izin->format('Y-m-d') . ' telah disetujui.';

        if ($this->izin->jenis_izin === \App\Enums\JenisIzin::PulangCepat
            || $this->izin->jenis_izin === 'pulang_cepat') {
            $message .= ' Anda dapat melakukan presensi pulang kapan saja.';
        }

        return [
            'type' => 'izin_approved',
            'izin_id' => $this->izin->id,
            'tgl_izin' => $this->izin->tgl_izin->format('Y-m-d'),
            'jenis_izin' => $jenisLabel,
            'message' => $message,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
