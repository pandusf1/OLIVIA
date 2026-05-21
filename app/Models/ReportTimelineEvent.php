<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportTimelineEvent extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id',
        'event_type',
        'event_message',
        'actor_type',
        'actor_id',
        'metadata',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'metadata' => 'array',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
