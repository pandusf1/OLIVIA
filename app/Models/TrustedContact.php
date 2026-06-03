<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TrustedContact extends Model
{
    use HasUuids;

    protected $table = 'trusted_contacts';

    protected $fillable = [
        'user_id',
        'contact_name',
        'contact_phone',
        'is_verified',
        'verification_code',
        'created_at'
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
