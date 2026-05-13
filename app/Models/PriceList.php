<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    protected $table = 'price_lists';


    protected $fillable = [
        'partner_id',
        'service_name',
        'price',
        'currency',
        'duration',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

}

