<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::guard('karyawan')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $notifications = $user->notifications()->latest()->get();

        $grouped = $notifications->groupBy(function ($n) {
            return $n->created_at->format('Y-m-d');
        })->map(function ($items, $date) {
            return [
                'date' => $date,
                'label' => Carbon::parse($date)->isToday() ? 'Hari Ini'
                    : (Carbon::parse($date)->isYesterday() ? 'Kemarin'
                    : Carbon::parse($date)->translatedFormat('d M Y')),
                'items' => $items,
            ];
        })->values();

        return view('karyawan.presensi.notifikasi', compact('grouped'));
    }

    public function read($id)
    {
        $user = Auth::guard('karyawan')->user();
        $n = $user->notifications()->where('id', $id)->first();
        if ($n) $n->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function readAll()
    {
        $user = Auth::guard('karyawan')->user();
        if (!$user) return response()->json(['ok' => false], 401);

        $user->unreadNotifications->markAsRead();
        return response()->json(['ok' => true]);
    }
}
