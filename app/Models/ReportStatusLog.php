<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportStatusLog extends Model
{
    use HasUuids;

    protected $table = 'report_status_logs';

    protected $fillable = [
        'report_id',
        'old_status',
        'new_status',
        'changed_by',
        'changed_at'
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
