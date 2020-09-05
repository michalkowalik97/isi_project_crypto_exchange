<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function getTypeTranslation(){
        $types = [
            'buy'=>'Kupno',
            'sell'=>'Sprzedaż'
        ];

        return (isset($types[$this->type])) ? $types[$this->type] : $this->type;
    }
}
