<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WitnessReport extends Model
{
    use HasUuids;

    protected $table = 'witness_reports';

    protected $fillable = [
        'report_id',
        'witness_name',
        'witness_phone',
        'witness_note',
        'created_at',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function evidences()
    {
        return $this->hasMany(WitnessEvidence::class, 'witness_report_id');
    }
}
