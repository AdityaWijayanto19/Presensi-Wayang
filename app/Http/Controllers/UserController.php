<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unitperusahaan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;

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

    public function store(StoreUserRequest $request)
    {

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->nama_user,
                'email' => $request->email,
                'unit' => $request->unit,
                'unit_id' => Unitperusahaan::where('unit', $request->unit)->value('id'),
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

    public function update(UpdateUserRequest $request, int $id)
    {

        $user = User::findOrFail($id);

        $updateData = [
            'name' => $request->nama_user,
            'email' => $request->email,
            'unit' => $request->unit,
            'unit_id' => Unitperusahaan::where('unit', $request->unit)->value('id'),
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
