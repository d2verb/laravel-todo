<?php

namespace Tests\Feature;

use App\Folder;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FolderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testGuestCannotViewFolderCreationForm()
    {
        $response = $this->get(route('folders.create'));
        $response->assertRedirect(route('login'));
    }

    public function testUserCanViewFolderCreationForm()
    {
        $response = $this->actingAs($this->user)->get(route('folders.create'));
        $response->assertSuccessful();
    }

    public function testUserCanCreateFolder()
    {
        $response = $this->actingAs($this->user)->post(route('folders.create'), [
            'title' => 'test folder',
        ]);

        $this->assertCount(1, $folders = Folder::all());
        $folder = $folders->first();
        $this->assertEquals('test folder', $folder->title);
        $this->assertEquals($this->user->id, $folder->user_id);
        $response->assertRedirect(route('tasks.index', $folder->id));
    }

    public function testUserCannotCreateFolderWithLongTitle()
    {
        $response = $this->actingAs($this->user)
                         ->from(route('folders.create'))
                         ->post(route('folders.create'), [
            'title' => str_repeat('a', 21),
        ]);

        $this->assertCount(0, $folders = Folder::all());
        $response->assertSessionHasErrors('title');
        $response->assertRedirect(route('folders.create'));
    }
}
