<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

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
        $tgl = is_string($this->wfh->tgl_wfh) ? $this->wfh->tgl_wfh : $this->wfh->tgl_wfh->format('Y-m-d');
        return [
            'type' => 'wfh_reminder_laporan',
            'wfh_id' => $this->wfh->id,
            'tgl_wfh' => $tgl,
            'message' => 'WFH tanggal ' . $tgl . ' belum upload laporan! Harap upload sebelum pukul 23:59 agar tidak ditandai sebagai Unpaid.',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function sendWebPush(object $notifiable): void
    {
        $tgl = is_string($this->wfh->tgl_wfh) ? $this->wfh->tgl_wfh : $this->wfh->tgl_wfh->format('Y-m-d');
        app(\App\Services\Shared\WebPushService::class)->send(
            $notifiable->nik,
            '⚠️ Reminder Upload Laporan',
            'WFH tanggal ' . $tgl . ' belum upload laporan! Upload sebelum pukul 23:59.',
            '/presensi/wfh/' . $this->wfh->id . '/laporan',
            'reminder-laporan-' . $this->wfh->id
        );
    }
}
