<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Market;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class CreateUserWallets
{

    private $wallets = [];

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $currencies = Market::select('first_currency')->get();
        if ($currencies && count($currencies) > 0) {
            $this->wallets = $currencies->unique();
        } else {
            $this->wallets = array(
                "NEU", "BSV", "FTO", "EXY", "LML", "LTC", "ZRX", "BTC", "AMLT", "ETH", "BOB", "GGC", "XRP", "DASH",
                "ALG", "XIN", "TRX", "REP", "KZC", "LSK", "NPXS", "BTG", "OMG", "SRN", "ZEC", "PAY", "GNT", "BCC",
                "BCP", "BAT", "GAME", "LINK", "XLM", "USDC", "MKR", "XBX"
            );
        }

    }

    /**
     * Handle the event.
     *
     * @param UserRegistered $event
     * @return void
     */
    public function handle(UserRegistered $event)
    {

        $insert = ["currency" => "PLN", "all_founds" => 0.0, "locked_founds" => 0.0, "available_founds" => 0.0, "user_id" => $event->user->id, "name" => "PLN", "type" => "cash"];
        foreach ($this->wallets as $wallet) {
            $insert[] = ["currency" => "$wallet", "all_founds" => 0.0, "locked_founds" => 0.0, "available_founds" => 0.0, "user_id" => $event->user->id, "name" => $wallet, "type" => "crypto"];
        }
        DB::table('wallets')->insert($insert);
    }
}
