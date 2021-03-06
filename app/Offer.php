<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Offer
 * @package App
 *
 * @property integer id
 * @property integer external_id
 * @property float amount
 * @property float initial_amount
 * @property float rate
 * @property boolean completed
 * @property string type
 * @property boolean active
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property integer user_id
 * @property integer market_id
 * @property DateTime deleted_at
 * @property float realise_rate
 * @property float locked_founds
 * @property integer wallet_id
 * @property Market market
 * @property Wallet wallet
 * @property OfferPart parts
 */
class Offer extends Model
{
    use SoftDeletes;

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function parts()
    {
        return $this->hasMany(OfferPart::class);
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
        $decimas = explode('.', $this->initial_amount);
        if (isset($decimas[1])) {
            if (intval($decimas[1]) == 0) {
                return $decimas[0];
            }
        }
        return $this->initial_amount;
    }


}
