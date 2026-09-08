<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unitperusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['unitperusahaan', 'roles'])
            ->get()
            ->map(function ($user) {
                $user->role = $user->roles->pluck('name')->first();
                $user->perusahaan = $user->unitperusahaan?->perusahaan;
                return $user;
            });

        $unitperusahaan = Unitperusahaan::orderBy('unit')->get();
        $role = Role::orderBy('id')->get();

        return view('admin.users.index', compact('users', 'unitperusahaan', 'role'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_user' => 'required',
            'email' => 'required|email|unique:users,email',
            'unit' => 'required|exists:unitperusahaan,unit',
            'role' => 'required|exists:roles,id',
            'password' => 'required|min:6',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->nama_user,
                'email' => $request->email,
                'unit' => $request->unit,
                'password' => $request->password,
            ]);

            $user->assignRole($request->role);

            DB::commit();

            return Redirect::back()->with('success', 'Data User / Admin Berhasil Disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with('error', 'Data User / Admin Gagal Disimpan');
        }
    }

    public function edit(Request $request)
    {
        $user = User::with('roles')->findOrFail($request->id_user);
        $unitperusahaan = Unitperusahaan::orderBy('unit')->get();
        $role = Role::orderBy('id')->get();

        return view('admin.users.edit', compact('unitperusahaan', 'role', 'user'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'password' => 'nullable|min:6',
        ], [
            'password.min' => 'Password minimal 6 karakter',
        ]);

        $user = User::findOrFail($id);

        $updateData = [
            'name' => $request->nama_user,
            'email' => $request->email,
            'unit' => $request->unit,
        ];

        if (!empty($request->password)) {
            $updateData['password'] = Hash::make($request->password);
        }

        DB::beginTransaction();

        try {
            $user->update($updateData);
            $user->syncRoles([$request->role]);

            DB::commit();

            return Redirect::back()->with('success', 'Data User / Admin Berhasil Diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with('error', 'Data User / Admin Gagal Diperbarui');
        }
    }

    public function resetpassword(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['password' => Hash::make('12345678')]);

        return Redirect::back()->with('success', 'Password berhasil direset');
    }

    public function delete(int $id)
    {
        $user = User::findOrFail($id);
        $user->roles()->detach();
        $user->delete();

        return Redirect::back()->with('success', 'Data User / Admin Berhasil Dihapus');
    }
}
