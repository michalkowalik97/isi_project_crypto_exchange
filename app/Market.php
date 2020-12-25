<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Market
 * @package App
 *
 * @property integer id
 * @property string market_code
 * @property string first_currency
 * @property string second_currency
 * @property integer time
 * @property boolean active
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property Offer offers
 */
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
