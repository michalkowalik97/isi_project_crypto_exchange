<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WalletTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testOpenExchange()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/wallets/paypal/1');

        $response->assertStatus(200);
    }

    public function testShowWallet()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/wallets/1');

        $response->assertStatus(200);
    }


}
