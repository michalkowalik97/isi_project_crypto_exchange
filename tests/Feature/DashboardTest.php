<?php

namespace Tests\Feature;


use App\User;
use Illuminate\Auth\Access\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    /** @test */
    public function is_auth_working()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
    }


    public function test_dashboard()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)-> get ('/');

        $response->assertSee('Dashboard');
    }
}
