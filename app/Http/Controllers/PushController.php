<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|url',
            'public_key' => 'required|string',
            'auth_token' => 'required|string',
        ]);

        $nik = Auth::guard('karyawan')->user()->nik;

        PushSubscription::updateOrCreate(
            ['nik' => $nik, 'endpoint' => $request->endpoint],
            [
                'public_key' => $request->public_key,
                'auth_token' => $request->auth_token,
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe()
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        PushSubscription::where('nik', $nik)->delete();

        return response()->json(['ok' => true]);
    }
}
