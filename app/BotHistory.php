<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BotHistory extends Model
{
    protected $fillable=['bot_job_id','offer_id','user_id'];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
