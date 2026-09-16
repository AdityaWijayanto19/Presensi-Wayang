<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LemburStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public $lembur, public $oldStatus, public $newStatus) {}

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
            'message' => 'Status lembur berubah dari ' . $this->oldStatus . ' ke ' . $this->newStatus,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
