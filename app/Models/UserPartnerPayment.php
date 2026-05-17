<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPartnerPayment extends Model
{
    protected $fillable = [
        'user_id',
        'partner_id',
        'price_list_id',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
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
