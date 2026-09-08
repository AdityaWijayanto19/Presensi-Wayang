<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $fillable = ['user_type', 'user_id', 'permission_name', 'is_enabled'];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public static function getPermissions(string $type, string $id): array
    {
        $permissions = static::where('user_type', $type)
            ->where('user_id', $id)
            ->get()
            ->pluck('is_enabled', 'permission_name')
            ->toArray();

        return [
            'location' => $permissions['location'] ?? true,
            'camera' => $permissions['camera'] ?? true,
            'notifications' => $permissions['notifications'] ?? true,
        ];
    }

    public static function toggle(string $type, string $id, string $permissionName): array
    {
        $record = static::firstOrCreate([
            'user_type' => $type,
            'user_id' => $id,
            'permission_name' => $permissionName,
        ], [
            'is_enabled' => true,
        ]);

        $record->update(['is_enabled' => !$record->is_enabled]);

        return [
            'permission' => $permissionName,
            'is_enabled' => $record->is_enabled,
        ];
    }
}
