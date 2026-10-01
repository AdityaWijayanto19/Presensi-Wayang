<?php

namespace App\Http\Controllers;

use App\Http\Requests\Push\SubscribePushRequest;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PushController extends Controller
{
    public function subscribe(SubscribePushRequest $request): JsonResponse
    {
        $nik = Auth::guard('karyawan')->user()->nik;

        // Di-key per endpoint, bukan per nik+endpoint: satu endpoint selalu satu baris.
        // Saat user lain login di perangkat yang sama, baris lama di-reassign ke user baru
        // sehingga sinkronisasi saat page load dapat memperbaiki data tanpa membuat baris ganda.
        $subscription = PushSubscription::updateOrCreate(
            ['endpoint' => $request->endpoint],
            [
                'nik' => $nik,
                'public_key' => $request->public_key,
                'auth_token' => $request->auth_token,
            ]
        );

        Log::info('push.subscribe.saved', [
            'nik' => $nik,
            'subscription_id' => $subscription->id,
            'host' => parse_url($request->endpoint, PHP_URL_HOST),
            'created' => $subscription->wasRecentlyCreated,
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Dikonsumsi Service Worker pada event pushsubscriptionchange.
     *
     * Berbentuk GET karena permintaan GET tidak divalidasi CSRF, sehingga Service Worker
     * tidak perlu menyimpan token di localStorage ataupun membaca cookie secara manual.
     * Respons hanya dapat dibaca oleh origin yang sama.
     */
    public function bootstrap(): JsonResponse
    {
        return response()->json([
            'public_key' => config('webpush.vapid.public_key'),
            'csrf' => csrf_token(),
        ]);
    }

    public function unsubscribe(): JsonResponse
    {
        $nik = Auth::guard('karyawan')->user()->nik;

        PushSubscription::where('nik', $nik)->delete();

        Log::info('push.unsubscribe', ['nik' => $nik]);

        return response()->json(['ok' => true]);
    }
}
