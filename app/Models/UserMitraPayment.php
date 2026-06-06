<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMitraPayment extends Model
{
    protected $table = 'user_mitra_payments';

    protected $fillable = [
        'user_id',
        'mitra_id',
        'price_list_id',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
