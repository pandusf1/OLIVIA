<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportChronology extends Model
{
    protected $table = 'report_chronologies';

    protected $fillable = [
        'report_id',
        'user_id',
        'writer_name',
        'role',
        'description',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
