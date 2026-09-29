<?php

namespace App\Models;

use App\Enums\ActivityAction;
use App\Enums\ActivityModule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivityLog extends Model
{
    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'user_name',
        'role',
        'module',
        'action',
        'entity_id',
        'description',
        'status',
        'method',
        'path',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'module' => ActivityModule::class,
        'action' => ActivityAction::class,
        'created_at' => 'datetime',
    ];

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
