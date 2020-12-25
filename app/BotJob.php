<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;


/**
 * Class BotJob
 *
 * @property integer id
 * @property float max_value
 * @property float min_profit
 * @property integer market_id
 * @property integer user_id
 * @property boolean active
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property DateTime deleted_at
 * @property integer previous_offer_id
 * @property integer offer_id
 * @property Market market - market related to BotJob
 * @property User user - BotJob owner
 * @property Offer offer - offer related to BotJob
 * @property Offer previousOffer - previous completed offer, used to calculate profit
 * @property Wallet fiatWallet - wallet with PLN currency
 * @property BotHistory history
 */
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

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function previousOffer()
    {
        return $this->hasOne(Offer::class, 'id', 'previous_offer_id');
    }

    public function fiatWallet()
    {
        return $this->hasOneThrough(
            Wallet::class,
            User::class,
            'id',
            'user_id',
            'user_id'
        )->where('currency', 'PLN');
    }

    public function history()
    {
        return $this->hasMany(BotHistory::class)->orderByDesc('created_at');
    }
}
