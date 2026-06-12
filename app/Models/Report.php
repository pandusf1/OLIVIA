<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\ReportStatusLog;
use App\Models\Mitra;
use App\Models\ReportMitraRouting;
use App\Models\ReportTimelineEvent;
class Report extends Model
{
    use HasUuids;

    protected $table = 'reports';

    protected $fillable = [
        'user_id',
        'report_type',
        'category',
        'description',
        'latitude',
        'longitude',
        'location_text',
        'incident_date',
        'anonymous',
        'status',
        'urgency_level',
        'routed_mitra_id',
        'handler_mitra_id',
        'handler_user_id',
        'assigned_at',
        'location_verified_at',
        'escalated_at',
        'last_activity_at',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'anonymous' => 'boolean',
        'assigned_at' => 'datetime',
        'location_verified_at' => 'datetime',
        'escalated_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function evidences()
    {
        return $this->hasMany(Evidence::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusLogs()
{
    return $this->hasMany(ReportStatusLog::class)
        ->orderBy('changed_at', 'asc');
}

public function mitra()
{
    return $this->belongsTo(Mitra::class, 'routed_mitra_id');
}

public function mitraRoutings()
{
    return $this->hasMany(ReportMitraRouting::class, 'report_id');
}

public function timelineEvents()
{
    return $this->hasMany(ReportTimelineEvent::class)->latest();
}

public function chronologies()
{
    return $this->hasMany(ReportChronology::class)->latest();
}

public function routingMitras()
{
    return $this->belongsToMany(Mitra::class, 'report_mitra_routings', 'report_id', 'mitra_id')
        ->withPivot(['status', 'routed_at', 'responded_at', 'expires_at'])
        ->withTimestamps();
}

public function assignedMitra()
{
    return $this->belongsTo(Mitra::class, 'handler_mitra_id');
}

    public function handlerUser()
    {
        return $this->belongsTo(User::class, 'handler_user_id');
    }

    /**
     * Petakan status database ke label Indonesia 1 kata yang profesional.
     */
    public function getStatusLabelIndonesianAttribute(): string
    {
        return match ($this->status) {
            'Submitted' => 'Diajukan',
            'Routed' => 'Diteruskan',
            'Viewed' => 'Ditinjau',
            'Assigned' => 'Diterima',
            'In Progress' => 'Diproses',
            'Resolved' => 'Selesai',
            'Rejected' => 'Ditolak',
            default => $this->status ?? 'Diajukan',
        };
    }
}
