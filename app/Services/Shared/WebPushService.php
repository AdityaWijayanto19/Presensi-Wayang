<?php

namespace App\Services\Shared;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    public function send(string $nik, string $title, string $body, ?string $url = null, ?string $tag = null): void
    {
        if (!config('webpush.enabled')) return;

        try {
            $subscriptions = PushSubscription::where('nik', $nik)->get();
            if ($subscriptions->isEmpty()) return;

            $auth = [
                'VAPID' => [
                    'subject' => config('webpush.vapid.subject'),
                    'publicKey' => config('webpush.vapid.public_key'),
                    'privateKey' => config('webpush.vapid.private_key'),
                ],
            ];

            $webPush = new WebPush($auth);

            foreach ($subscriptions as $sub) {
                $payload = json_encode([
                    'title' => $title,
                    'body' => $body,
                    'url' => $url ?? '/dashboard',
                    'tag' => $tag,
                ]);

                $subscription = new \Minishlink\WebPush\Subscription(
                    $sub->endpoint,
                    $sub->public_key,
                    $sub->auth_token
                );

                $webPush->sendOneNotification($subscription, $payload, ['TTL' => 3600]);
            }
        } catch (\Exception $e) {
            Log::warning('Web push failed: ' . $e->getMessage());
        }
    }
}
