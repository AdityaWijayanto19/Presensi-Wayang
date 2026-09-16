<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LemburReminderLaporan extends Notification
{
    use Queueable;

    public function __construct(public $lembur) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'lembur_reminder_laporan',
            'lembur_id' => $this->lembur->id,
            'tgl_lembur' => $this->lembur->tgl_lembur->format('Y-m-d'),
            'message' => 'Reminder: lembur tanggal ' . $this->lembur->tgl_lembur->format('Y-m-d') . ' belum upload laporan',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
