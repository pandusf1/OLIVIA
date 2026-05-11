<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Evidence extends Model
{
    use HasUuids;

    protected $table = 'evidences';

    protected $fillable = [
        'report_id',
        'file_url',
        'file_type',
        'file_hash',
        'uploaded_by',
        'uploaded_at'
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}