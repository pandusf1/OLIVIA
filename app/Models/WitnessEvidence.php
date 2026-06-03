<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WitnessEvidence extends Model
{
    use HasUuids;

    protected $table = 'witness_evidences';

    protected $fillable = [
        'witness_report_id',
        'file_url',
        'file_type',
        'file_hash',
        'uploaded_at',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];
}
