<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GaTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testOpenF2a()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/account/settings/f2a');

        $response->assertSee('Funkcja uwierzytelnienia dwuskładnikowego jest obecnie niedostępna.');
    }
}
