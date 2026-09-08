<?php

namespace App\Http\Controllers;

use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPermissionController extends Controller
{
    public function karyawanSettings()
    {
        $user = Auth::guard('karyawan')->user();
        $permissions = UserPermission::getPermissions('karyawan', $user->nik);
        return view('karyawan.settings', compact('user', 'permissions'));
    }

    public function adminSettings()
    {
        $user = Auth::guard('user')->user();
        $permissions = UserPermission::getPermissions('admin', (string) $user->id);
        return view('admin.settings', compact('user', 'permissions'));
    }

    public function getPermissions()
    {
        $user = Auth::guard('karyawan')->user();
        return response()->json(UserPermission::getPermissions('karyawan', $user->nik));
    }

    public function togglePermission(Request $request)
    {
        $request->validate([
            'permission' => 'required|in:location,camera,notifications',
        ]);

        $user = Auth::guard('karyawan')->user();
        $result = UserPermission::toggle('karyawan', $user->nik, $request->permission);

        return response()->json($result);
    }

    public function adminGetPermissions()
    {
        $user = Auth::guard('user')->user();
        return response()->json(UserPermission::getPermissions('admin', (string) $user->id));
    }

    public function adminTogglePermission(Request $request)
    {
        $request->validate([
            'permission' => 'required|in:location,camera,notifications',
        ]);

        $user = Auth::guard('user')->user();
        $result = UserPermission::toggle('admin', (string) $user->id, $request->permission);

        return response()->json($result);
    }
}
