<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\ReportStatusLog;
use App\Models\Partner;
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
        'anonymous',
        'status',
        'routed_partner_id',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

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
}
