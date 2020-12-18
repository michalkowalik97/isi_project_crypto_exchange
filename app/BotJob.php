<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BotJob extends Model
{
    protected $fillable = ['max_value', 'min_profit', 'market_id'];

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function offer(){
        return $this->belongsTo(Offer::class);
    }

    public function fiatWallet()
    {
        return $this->hasOneThrough(
            Wallet::class,
            User::class,
            'id'
        )->where('currency','PLN');

    }
}
