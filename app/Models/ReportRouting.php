<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportRouting extends Model
{
    use HasUuids;

    protected $table = 'report_routing';

    protected $fillable = [
        'report_id',
        'partner_id',
        'routed_at',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $casts = [
        'routed_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
