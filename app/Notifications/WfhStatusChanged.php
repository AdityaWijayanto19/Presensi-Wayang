<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WfhStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public $wfh,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $tgl = is_string($this->wfh->tgl_wfh) ? $this->wfh->tgl_wfh : $this->wfh->tgl_wfh->format('Y-m-d');
        $statusLabels = [
            'pending_atasan' => 'Menunggu Atasan',
            'pending_admin' => 'Menunggu HR',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'unpaid' => 'Unpaid',
        ];
        $oldLabel = $statusLabels[$this->oldStatus] ?? $this->oldStatus;
        $newLabel = $statusLabels[$this->newStatus] ?? $this->newStatus;

        return [
            'type' => 'wfh_status_changed',
            'wfh_id' => $this->wfh->id,
            'tgl_wfh' => $tgl,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => 'WFH tanggal ' . $tgl . ' status diubah dari "' . $oldLabel . '" menjadi "' . $newLabel . '" oleh Admin.',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
