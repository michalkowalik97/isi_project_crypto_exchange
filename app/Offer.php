<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use SoftDeletes;

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function getTypeTranslation()
    {
        $types = [
            'buy' => 'Kupno',
            'sell' => 'Sprzedaż'
        ];

        return (isset($types[$this->type])) ? $types[$this->type] : $this->type;
    }

    public function displayAmount()
    {
        $decimas = explode('.', $this->amount);
        if (isset($decimas[1])) {
            if (intval($decimas[1]) == 0) {
                return $decimas[0];
            }
        }
        return $this->amount;

    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

}
