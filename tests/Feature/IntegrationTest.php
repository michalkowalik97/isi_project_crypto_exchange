<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIntegrationIndex()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/account/settings/integration');

        $response->assertSee('Aby dodać integrację potrzebujesz konta na giełdzie');
    }
    public function testIntegrationCreate()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/account/settings/integration/create');

        $response->assertSee('Aby dodać integrację potrzebujesz konta na giełdzie');
    }
}
