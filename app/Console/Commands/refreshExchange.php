<?php

namespace App\Console\Commands;

use App\BotHistory;
use App\BotJob;
use App\Offer;
use App\OfferPart;
use App\Wallet;
use Illuminate\Console\Command;

class refreshExchange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange:fresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear offers, set wallets values to 1000, and restart bot jobs.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $wallets = Wallet::withTrashed()->get();
        $offers = Offer::withTrashed()->get();
        $botJobs = BotJob::withTrashed()->get();
        $botHistory = BotHistory::withTrashed()->get();
        $offerParts = OfferPart::withTrashed()->get();
        $bar = $this->output->createProgressBar((count($wallets) + count($offers) + count($botJobs) + count($botHistory) + count($offerParts) ));

        $bar->start();

        foreach ($wallets as $wallet) {
            $wallet->all_founds = 1000;
            $wallet->locked_founds = 0;
            $wallet->available_founds = 1000;
            $wallet->save();
            $bar->advance();
        }

        foreach ($offers as $offer) {
            $offer->forceDelete();
            $bar->advance();
        }
        foreach ($offerParts as $part) {
            $part->forceDelete();
            $bar->advance();
        }

        foreach ($botJobs as $job){
            $job->offer_id=null;
            $job->save();
            $bar->advance();
        }

        foreach ($botHistory as $history){
            $history->forceDelete();
            $bar->advance();
        }

        $bar->finish();
        $this->line("\n Exchange refreshed!");
    }
}
