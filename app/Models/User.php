<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'mitra_id', 'google_id',
        'phone_is_verified', 'phone_verification_code',
        'receive_nearby_alerts', 'nearby_alert_count', 'next_nearby_alert_threshold',
    ];

    protected $hidden = [
        'password', 'remember_token', 'phone_verification_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_is_verified' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function trustedContacts(): HasMany
    {
        return $this->hasMany(TrustedContact::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function userLocation()
    {
        return $this->hasOne(UserLocation::class, 'user_id', 'id');
    }



    public function isUser()
    {
        return $this->role === 'user';
    }

    public function isMitra(): bool
    {
        return in_array($this->role, ['mitra', 'admin']);
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
