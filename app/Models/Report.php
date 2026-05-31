<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\ReportStatusLog;
use App\Models\Partner;
use App\Models\ReportPartnerRouting;
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
        'routed_partner_id',
        'handler_partner_id',
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

    public function witnessReports()
    {
        return $this->hasMany(WitnessReport::class);
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

public function partner()
{
    return $this->belongsTo(Partner::class, 'routed_partner_id');
}

public function partnerRoutings()
{
    return $this->hasMany(ReportPartnerRouting::class);
}

public function timelineEvents()
{
    return $this->hasMany(ReportTimelineEvent::class)->latest();
}

public function chronologies()
{
    return $this->hasMany(ReportChronology::class)->latest();
}

public function routingPartners()
{
    return $this->belongsToMany(Partner::class, 'report_partner_routings')
        ->withPivot(['status', 'routed_at', 'responded_at', 'expires_at'])
        ->withTimestamps();
}

public function assignedPartner()
{
    return $this->belongsTo(Partner::class, 'handler_partner_id');
}

public function handlerUser()
{
    return $this->belongsTo(User::class, 'handler_user_id');
}
}
