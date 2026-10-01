<?php

namespace App\Services\Shared;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\ContentEncoding;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    /**
     * Kirim satu Web Push ke seluruh perangkat yang terdaftar untuk satu NIK.
     *
     * Tidak pernah melempar exception: kegagalan pengiriman wajib mengganggu
     * alur bisnis pemanggil (persetujuan izin, lembur, WFH, dst).
     */
    public function send(string $nik, string $title, string $body, ?string $url = null, ?string $tag = null): void
    {
        if (! config('webpush.enabled')) {
            return;
        }

        try {
            $subscriptions = PushSubscription::where('nik', $nik)->get();

            if ($subscriptions->isEmpty()) {
                Log::info('webpush.skipped', ['nik' => $nik, 'reason' => 'no_subscription']);

                return;
            }

            $webPush = $this->makeClient();

            foreach ($subscriptions as $subscription) {
                $this->deliver($webPush, $nik, $subscription, $title, $body, $url, $tag);
            }
        } catch (\Throwable $e) {
            Log::error('webpush.failed', [
                'nik' => $nik,
                'reason' => 'client_error',
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function makeClient(): WebPush
    {
        $client = new WebPush([
            'VAPID' => [
                'subject' => config('webpush.vapid.subject'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ]);

        $client->setDefaultOptions([
            'TTL' => (int) config('webpush.ttl', 86400),
            'urgency' => (string) config('webpush.urgency', 'high'),
        ]);

        return $client;
    }

    /**
     * Kirim ke satu perangkat. Kegagalan di sini dibatasi pada perangkat itu saja
     * agar perangkat lain tetap menerima notifikasi.
     */
    private function deliver(
        WebPush $webPush,
        string $nik,
        PushSubscription $subscription,
        string $title,
        string $body,
        ?string $url,
        ?string $tag
    ): void {
        $context = [
            'nik' => $nik,
            'subscription_id' => $subscription->id,
            'endpoint' => $this->maskEndpoint($subscription->endpoint),
        ];

        try {
            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'url' => $url ?? '/dashboard',
                'tag' => $tag,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $report = $webPush->sendOneNotification(
                $this->makeSubscription($subscription),
                $payload
            );
        } catch (\Throwable $e) {
            Log::warning('webpush.failed', $context + [
                'reason' => 'transport_error',
                'error' => $e->getMessage(),
            ]);

            return;
        }

        $this->handleReport($report, $context);
    }

    private function handleReport(MessageSentReport $report, array $context): void
    {
        if ($report->isSuccess()) {
            Log::info('webpush.sent', $context);

            return;
        }

        // 404 / 410 = endpoint sudah mati. Hanya baris ini yang dihapus,
        // tidak pernah seluruh subscription milik satu user.
        if ($report->isSubscriptionExpired()) {
            PushSubscription::where('id', $context['subscription_id'])->delete();

            Log::warning('webpush.expired', $context + [
                'status' => $this->statusOf($report),
            ]);

            return;
        }

        // Sisanya (401/403 VAPID, 413 payload, 429 rate limit, dsb) adalah masalah
        // konfigurasi server, bukan perangkat mati. Subscription tetap disimpan.
        Log::warning('webpush.failed', $context + [
            'status' => $this->statusOf($report),
            'reason' => $report->getReason(),
        ]);
    }

    /**
     * aes128gcm (RFC 8291) adalah encoding yang diwajibkan Safari/iOS.
     * Default library adalah aesgcm yang sudah usang.
     */
    private function makeSubscription(PushSubscription $subscription): Subscription
    {
        return new Subscription(
            $subscription->endpoint,
            $subscription->public_key,
            $subscription->auth_token,
            ContentEncoding::aes128gcm
        );
    }

    private function statusOf(MessageSentReport $report): ?int
    {
        $response = $report->getResponse();

        return $response?->getStatusCode();
    }

    /**
     * Endpoint berisi token perangkat — cukup log host + ekor pendek untuk diagnosis.
     */
    private function maskEndpoint(string $endpoint): string
    {
        $host = parse_url($endpoint, PHP_URL_HOST) ?: 'unknown';

        return $host . '/…' . substr($endpoint, -6);
    }
}
