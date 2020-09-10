<?php

namespace Tests\Feature;

use App\Http\Controllers\ExchangeController;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExchangeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testOpenExchange()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/exchange');

        $response->assertSee('Twoje saldo');
    }

    public function testOpenOffers()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/exchange/offers/active');

        $response->assertSee('Oferty');
    }

    public function testSelectMarket()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->post('/select/market/',['market'=>'BTC-PLN']);
        $response->assertStatus(302);
    }

    public function testOrderbook()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/exchange/get/orderbook/BTC-PLN');

        $response->assertJson([
            'success' => true,
        ]);
    }

    public function testUpdateMarkets()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/exchange/update/markets');

        $response->assertStatus(200);
    }

   public function testOffersCheck()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/exchange/offers/check');

        $response->assertStatus(200);
    }

}
