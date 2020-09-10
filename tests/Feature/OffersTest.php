<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OffersTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testActiveOffers()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/exchange/offers/active');

        $response->assertStatus(200);
    }

    public function testHistoryOffers()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/exchange/offers/history');

        $response->assertStatus(200);
    }
}
