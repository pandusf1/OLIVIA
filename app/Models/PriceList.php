<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    protected $table = 'price_lists';


    protected $fillable = [
        'mitra_id',
        'service_name',
        'price',
        'currency',
        'duration',
    ];

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }

    public function payments()
    {
        return $this->hasMany(UserMitraPayment::class);
    }

}
