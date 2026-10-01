<?php

namespace App\Services;

class NotificationLinkService
{
    public static function resolve(object $notification): string
    {
        $data = $notification->data ?? [];

        if (! empty($data['url'])) {
            return (string) $data['url'];
        }

        $type = (string) ($data['type'] ?? '');

        return match (true) {
            $type === 'izin_rejected' => self::withId($data['izin_id'] ?? null, '/izin/%s/edit', '/izin'),
            $type === 'laporan_rejected' => self::withId($data['wfh_id'] ?? null, '/wfh/%s/laporan/edit', '/wfh'),
            $type === 'laporan_lembur_rejected' => self::withId($data['lembur_id'] ?? null, '/lembur/%s/laporan/edit', '/lembur'),
            $type === 'wfh_reminder_laporan' => self::withId($data['wfh_id'] ?? null, '/wfh/%s/laporan', '/wfh'),
            $type === 'lembur_reminder_laporan' => self::withId($data['lembur_id'] ?? null, '/lembur/%s/laporan', '/lembur'),
            $type === 'wfh_h1_reminder' => '/presensi/create',
            in_array($type, [
                'izin_submitted',
                'wfh_submitted',
                'laporan_submitted',
                'laporan_lembur_submitted',
                'lembur_submitted',
            ], true) => '/dashboard',
            str_starts_with($type, 'izin_') => '/izin',
            str_starts_with($type, 'laporan_lembur_') => '/lembur',
            str_starts_with($type, 'laporan_') => '/wfh',
            str_starts_with($type, 'lembur_') => '/lembur',
            str_starts_with($type, 'wfh_') => '/wfh',
            default => '/dashboard',
        };
    }

    private static function withId(mixed $id, string $pattern, string $fallback): string
    {
        return filled($id) ? sprintf($pattern, $id) : $fallback;
    }
}
