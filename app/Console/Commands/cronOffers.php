<?php

namespace App\Console\Commands;

use App\Http\Controllers\ExchangeController;
use Illuminate\Console\Command;

class cronOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        for ($i=0; $i< 12;$i++) {
            app(ExchangeController::class)->checkOffers();
            sleep(4);
        }
    }
}
