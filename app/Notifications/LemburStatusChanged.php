<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LemburStatusChanged extends Notification
{
    use Queueable;

    private const STATUS_LABELS = [
        'pending_atasan' => 'Menunggu Persetujuan Atasan',
        'pending_admin' => 'Menunggu Persetujuan HR',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'unpaid' => 'Unpaid',
        'upload_laporan' => 'Diminta Upload Laporan',
    ];

    public function __construct(
        public $lembur,
        public $oldStatus,
        public $newStatus,
        /** Pesan custom; kalau null pakai format baku "berubah dari X ke Y". */
        public ?string $customMessage = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'lembur_status_changed',
            'lembur_id' => $this->lembur->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'tgl_lembur' => $this->lembur->tgl_lembur->format('Y-m-d'),
            'message' => $this->customMessage
                ?? ('Status lembur berubah dari ' . (self::STATUS_LABELS[$this->oldStatus] ?? $this->oldStatus) . ' ke ' . (self::STATUS_LABELS[$this->newStatus] ?? $this->newStatus)),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
