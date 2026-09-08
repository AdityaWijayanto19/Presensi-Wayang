<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Services\WfhService;

class WfhReminderLaporan extends Notification
{
    use Queueable;

    public function __construct(public $wfh) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'wfh_reminder_laporan',
            'wfh_id' => $this->wfh->id,
            'tgl_wfh' => $this->wfh->tgl_wfh,
            'message' => 'WFH tanggal ' . $this->wfh->tgl_wfh . ' belum upload laporan! Harap upload sebelum pukul 00:00 agar tidak ditandai sebagai Unpaid.',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function sendWebPush(object $notifiable): void
    {
        WfhService::sendWebPush(
            $notifiable->nik,
            '⚠️ Reminder Upload Laporan',
            'WFH tanggal ' . $this->wfh->tgl_wfh . ' belum upload laporan! Upload sebelum pukul 00:00.',
            '/presensi/wfh/' . $this->wfh->id . '/laporan',
            'reminder-laporan-' . $this->wfh->id
        );
    }
}
