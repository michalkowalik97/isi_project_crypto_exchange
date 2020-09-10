<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testOpenAccount()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/account/settings');

        $response->assertStatus(200);
    }

    public function testChangePassword()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/account/settings/change/password');

        $response->assertStatus(200);
    }

    public function testLogin()
    {
        $response = $this->get('/login');

        $response->assertSee('Zaloguj się');
    }

    public function testResetPassword()
    {
        $response = $this->get('/password/reset');

        $response->assertSee('Zresetuj hasło');
    }

    public function testRegister()
    {
        $response = $this->get('/register');

        $response->assertSee('Zarejestruj się');
    }
}
