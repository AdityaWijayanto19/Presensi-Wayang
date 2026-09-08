<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $table = 'push_subscriptions';
    public $timestamps = true;

    protected $fillable = [
        'nik',
        'endpoint',
        'public_key',
        'auth_token',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }
}
