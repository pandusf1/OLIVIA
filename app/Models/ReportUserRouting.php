<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportUserRouting extends Model
{
    use HasUuids;

    protected $table = 'report_user_routing';

    protected $fillable = [
        'report_id',
        'target_user_id',
        'routed_at',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}


