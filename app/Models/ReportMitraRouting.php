<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportMitraRouting extends Model
{
    use HasUuids;

    protected $table = 'report_mitra_routings';

    protected $fillable = [
        'report_id',
        'mitra_id',
        'status',
        'routed_at',
        'reviewed_at',
        'responded_at',
        'expires_at',
        'distance_km',
        'estimated_response_minutes',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'routed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'responded_at' => 'datetime',
        'expires_at' => 'datetime',
        'distance_km' => 'float',
        'estimated_response_minutes' => 'integer',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }
}
