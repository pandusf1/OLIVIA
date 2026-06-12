<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditLog extends Model
{
    use HasUuids;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'target_type',
        'target_id',
        'created_at',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Fungsi helper singkat untuk mencatat aktivitas (log).
     */
    public static function log(string $action, string $targetType = null, string $targetId = null): void
    {
        static::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'created_at'  => now(),
        ]);
    }
}
