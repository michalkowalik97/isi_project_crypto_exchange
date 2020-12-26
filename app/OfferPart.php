<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OfferPart
 * @package App
 *
 * @property integer id
 * @property integer offer_id
 * @property float amount
 * @property float rate
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property integer matched_offer_id
 * @property string matched_offer_hash
 * @property Offer offer
 * @property Offer mathedOffer
 */

class OfferPart extends Model
{

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function mathedOffer()
    {
        $this->hasOne(Offer::class,'id','matched_offer_id');
    }
}
