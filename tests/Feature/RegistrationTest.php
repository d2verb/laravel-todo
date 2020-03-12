<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCanViewRegistrationForm()
    {
        $response = $this->get(route('register'));

        $response->assertSuccessful();
        $response->assertViewIs('auth.register');
    }

    public function testUserCannotViewRegistrationForm()
    {
        $user = factory(User::class)->make();
        $response = $this->actingAs($user)->get(route('register'));
        $response->assertRedirect(route('home'));
    }

    public function testUserCanRegister()
    {
        $this->assertGuest();
        $response = $this->post('/register', [
            'email' => 'fake@example.com',
            'name' => 'fake',
            'password' => 'fakefake',
            'password_confirmation' => 'fakefake',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertCount(1, $users = User::all());
        $this->assertAuthenticatedAs($user = $users->first());
        $this->assertEquals('fake', $user->name);
        $this->assertEquals('fake@example.com', $user->email);
        $this->assertEquals(true, Hash::check('fakefake', $user->password));
    }

    public function testUserCannotRegisterWithoutEmail()
    {
        $this->assertGuest();
        $response = $this->from(route('register'))->post('/register', [
            'email' => '',
            'name' => 'fake',
            'password' => 'fakefake',
            'password_confirmation' => 'fakefake',
        ]);

        $this->assertCount(0, $users = User::all());
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('email');
        $this->assertEquals(true, session()->hasOldInput('name'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithoutName()
    {
        $this->assertGuest();
        $response = $this->from(route('register'))->post('/register', [
            'email' => 'fake@example.com',
            'name' => '',
            'password' => 'fakefake',
            'password_confirmation' => 'fakefake',
        ]);

        $this->assertCount(0, $users = User::all());
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('name');
        $this->assertEquals(true, session()->hasOldInput('email'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithoutPassword()
    {
        $this->assertGuest();
        $response = $this->from(route('register'))->post('/register', [
            'email' => 'fake@example.com',
            'name' => 'fake',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $this->assertCount(0, $users = User::all());
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('password');
        $this->assertEquals(true, session()->hasOldInput('email'));
        $this->assertEquals(true, session()->hasOldInput('name'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithoutPasswordConfirmation()
    {
        $this->assertGuest();
        $response = $this->from(route('register'))->post('/register', [
            'email' => 'fake@example.com',
            'name' => 'fake',
            'password' => 'fakefake',
            'password_confirmation' => '',
        ]);

        $this->assertCount(0, $users = User::all());
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('password');
        $this->assertEquals(true, session()->hasOldInput('email'));
        $this->assertEquals(true, session()->hasOldInput('name'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithPasswordNotMatching()
    {
        $this->assertGuest();
        $response = $this->from(route('register'))->post('/register', [
            'email' => 'fake@example.com',
            'name' => 'fake',
            'password' => 'fakefake',
            'password_confirmation' => 'f4kefake',
        ]);

        $this->assertCount(0, $users = User::all());
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('password');
        $this->assertEquals(true, session()->hasOldInput('email'));
        $this->assertEquals(true, session()->hasOldInput('name'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithInvalidEmail()
    {
        $this->assertGuest();
        $response = $this->from(route('register'))->post('/register', [
            'email' => 'fakeexample.com',
            'name' => 'fake',
            'password' => 'fakefake',
            'password_confirmation' => 'fakefake',
        ]);

        $this->assertCount(0, $users = User::all());
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('email');
        $this->assertEquals(true, session()->hasOldInput('email'));
        $this->assertEquals(true, session()->hasOldInput('name'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithLongName()
    {
        $this->assertGuest();
        $response = $this->from(route('register'))->post('/register', [
            'email' => 'fakeexample.com',
            'name' => str_repeat('a', 256),
            'password' => 'fakefak',
            'password_confirmation' => 'fakefak',
        ]);

        $this->assertCount(0, $users = User::all());
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('password');
        $this->assertEquals(true, session()->hasOldInput('email'));
        $this->assertEquals(true, session()->hasOldInput('name'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithShortPassword()
    {
        $this->assertGuest();
        $response = $this->from(route('register'))->post('/register', [
            'email' => 'fakeexample.com',
            'name' => 'fake',
            'password' => 'fakefak',
            'password_confirmation' => 'fakefak',
        ]);

        $this->assertCount(0, $users = User::all());
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('password');
        $this->assertEquals(true, session()->hasOldInput('email'));
        $this->assertEquals(true, session()->hasOldInput('name'));
        $this->assertGuest();
    }

    public function testUserCannotRegisterWithLongPassword()
    {
        $this->assertGuest();
        $response = $this->from(route('register'))->post('/register', [
            'email' => 'fakeexample.com',
            'name' => 'fake',
            'password' => str_repeat('a', 256),
            'password_confirmation' => 'fakefak',
        ]);

        $this->assertCount(0, $users = User::all());
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('password');
        $this->assertEquals(true, session()->hasOldInput('email'));
        $this->assertEquals(true, session()->hasOldInput('name'));
        $this->assertGuest();
    }
}
