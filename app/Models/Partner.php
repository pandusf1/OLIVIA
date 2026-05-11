<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Partner extends Model
{
    use HasUuids;

    protected $table = 'partners';

    protected $fillable = [
        'partner_name',
        'partner_type',
        'city',
        'phone',
        'email',
        'verified',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $casts = [
        'verified' => 'boolean',
    ];

    public function reportRoutings()
    {
        return $this->hasMany(ReportRouting::class);
    }

    public static function routeByCategory($category)
{
    return match(strtolower($category)) {

        'salah tangkap' =>
            self::where('partner_type', 'legal')
                ->where('verified', true)
                ->first(),

        'pelecehan' =>
            self::where('partner_type', 'legal')
                ->where('verified', true)
                ->first(),

        'kecelakaan' =>
            self::where('partner_type', 'ambulance')
                ->where('verified', true)
                ->first(),

        'kekerasan' =>
            self::where('partner_type', 'counselor')
                ->where('verified', true)
                ->first(),

        default => null,
    };
}
}
