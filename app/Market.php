<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    protected $fillable = ['market_code',
        'first_currency',
        'second_currency',
        'time',
        'active'];

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }
}
