<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testGuestCanViewLoginForm()
    {
        $response = $this->get(route('login'));
        $response->assertSuccessful();
        $response->assertViewIs('auth.login');
    }

    public function testUserCannotViewLoginForm()
    {
        $response = $this->actingAs($this->user)->get(route('login'));
        $response->assertRedirect(route('home'));
    }

    public function testGuestCanLogin()
    {
        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        $this->assertAuthenticatedAs($this->user);
        $response->assertRedirect(route('home'));
    }

    public function testGuestCannotLoginWithoutEmail()
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => '',
            'password' => 'password',
        ]);
        $response->assertSessionHasErrors('email');
        $response->assertRedirect(route('login'));
    }

    public function testGuestCannotLoginWithoutPassword()
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => $this->user->email,
            'password' => '',
        ]);
        $this->assertEquals(true, session()->hasOldInput('email'));
        $response->assertRedirect(route('login'));
    }

    public function testUserCanLogout()
    {
        $response = $this->actingAs($this->user);
        $response->post(route('logout'));
        $this->assertGuest();
    }
}
