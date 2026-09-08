<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    // =====================================================
    // AUTHENTIKASI ADMIN
    // =====================================================

    // LOGIN ADMIN
    public function prosesloginadmin(Request $request)
    {
        if (
            Auth::guard('user')->attempt([
                'email' => $request->email,
                'password' => $request->password
            ])
        ) {
            return redirect('/panel/dashboard');
        }

        return redirect('/panel')
            ->with([
                'danger' => 'Email / Password Salah!'
            ]);
    }

    // LOGOUT ADMIN
    public function proseslogoutadmin()
    {
        if (Auth::guard('user')->check()) {

            Auth::guard('user')->logout();

            return redirect('/panel');
        }
    }



    // =====================================================
    // AUTHENTIKASI KARYAWAN
    // =====================================================

    // LOGIN KARYAWAN
    public function proseslogin(Request $request)
    {
        if (
            Auth::guard('karyawan')->attempt([
                'nik' => $request->nik,
                'password' => $request->password
            ])
        ) {
            return redirect('/dashboard');
        }

        return redirect('/')
            ->with([
                'warning' => 'NIK / Password Salah!'
            ]);
    }

    // LOGOUT KARYAWAN
    public function proseslogout()
    {
        if (Auth::guard('karyawan')->check()) {
            $nik = Auth::guard('karyawan')->user()->nik;
            DB::table('push_subscriptions')->where('nik', $nik)->delete();

            Auth::guard('karyawan')->logout();

            return redirect('/');
        }
    }
}
