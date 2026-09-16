<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LaporanLemburApprovedByAtasan extends Notification
{
    use Queueable;

    public function __construct(public $lembur, public $atasan) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'laporan_lembur_approved_by_atasan',
            'lembur_id' => $this->lembur->id,
            'atasan_nik' => $this->atasan->nik,
            'atasan_nama' => $this->atasan->nama_lengkap,
            'tgl_lembur' => $this->lembur->tgl_lembur->format('Y-m-d'),
            'message' => 'Laporan lembur disetujui atasan',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
