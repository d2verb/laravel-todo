<?php

namespace Tests\Feature;

use App\Folder;
use App\User;
use App\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->folder = factory(Folder::class)->create();
        $this->folder->user_id = $this->user->id;
    }

    public function testGuestCannotViewTaskCreationForm()
    {
        $response = $this->get(route('tasks.create', ['folder' => $this->folder->id]));
        $response->assertRedirect(route('login'));
    }

    public function testUserCanViewTaskCreationForm()
    {
        $response = $this->actingAs($this->user)
                         ->get(route('tasks.create', ['folder' => $this->folder->id]));
        $response->assertSuccessful();
        $response->assertViewIs('tasks.create');
    }

    public function testUserCanCreateTask()
    {
        $response = $this->actingAs($this->user)
                         ->post(route('tasks.create', ['folder' => $this->folder->id]), [
                             'title' => 'test task',
                             'due_date' => Carbon::tomorrow()->format('Y/m/d'),
                         ]);
        $this->assertCount(1, $tasks = Task::all());
        $task = $tasks->first();
        $this->assertEquals('test task', $task->title);
        $this->assertEquals($this->folder->id, $task->folder_id);
        $response->assertRedirect(route('tasks.index', $this->folder->id));
    }

    public function testUserCannotCreateTaskWithoutTitle()
    {
        $response = $this->actingAs($this->user)
                         ->from(route('tasks.create', ['folder' => $this->folder->id]))
                         ->post(route('tasks.create', ['folder' => $this->folder->id]), [
                             'title' => '',
                             'due_date' => Carbon::tomorrow()->format('Y/m/d'),
                         ]);
        $this->assertCount(0, $tasks = Task::all());
        $response->assertSessionHasErrors('title');
        $response->assertRedirect(route('tasks.create', ['folder' => $this->folder->id]));
    }

    public function testUserCannotCreateTaskWithLongTitle()
    {
        $response = $this->actingAs($this->user)
                         ->from(route('tasks.create', ['folder' => $this->folder->id]))
                         ->post(route('tasks.create', ['folder' => $this->folder->id]), [
                             'title' => str_repeat('a', 101),
                             'due_date' => Carbon::tomorrow()->format('Y/m/d'),
                         ]);
        $this->assertCount(0, $tasks = Task::all());
        $response->assertSessionHasErrors('title');
        $this->assertEquals(true, session()->hasOldInput('title'));
        $response->assertRedirect(route('tasks.create', ['folder' => $this->folder->id]));
    }

    public function testUserCannotCreateTaskWithoutDueDate()
    {
        $response = $this->actingAs($this->user)
                         ->from(route('tasks.create', ['folder' => $this->folder->id]))
                         ->post(route('tasks.create', ['folder' => $this->folder->id]), [
                             'title' => 'test task',
                             'due_date' => '',
                         ]);
        $this->assertCount(0, $tasks = Task::all());
        $response->assertSessionHasErrors('due_date');
        $response->assertRedirect(route('tasks.create', ['folder' => $this->folder->id]));
    }

    public function testUserCannotCreateTaskWithInvalidValueInDueDate()
    {
        $response = $this->actingAs($this->user)
                         ->from(route('tasks.create', ['folder' => $this->folder->id]))
                         ->post(route('tasks.create', ['folder' => $this->folder->id]), [
                             'title' => 'test task',
                             'due_date' => 123,
                         ]);
        $this->assertCount(0, $tasks = Task::all());
        $response->assertSessionHasErrors('due_date');
        $this->assertEquals(true, session()->hasOldInput('title'));
        $response->assertRedirect(route('tasks.create', ['folder' => $this->folder->id]));
    }

    public function testUserCannotCreateTaskWithPastDueDate()
    {
        $response = $this->actingAs($this->user)
                         ->from(route('tasks.create', ['folder' => $this->folder->id]))
                         ->post(route('tasks.create', ['folder' => $this->folder->id]), [
                             'title' => 'test task',
                             'due_date' => Carbon::yesterday()->format('Y/m/d'),
                         ]);
        $this->assertCount(0, $tasks = Task::all());
        $response->assertSessionHasErrors('due_date');
        $this->assertEquals(true, session()->hasOldInput('title'));
        $response->assertRedirect(route('tasks.create', ['folder' => $this->folder->id]));
    }

    public function testUserCanRemoveTask()
    {
        $response = $this->actingAs($this->user)
                         ->post(route('tasks.create', ['folder' => $this->folder->id]), [
                             'title' => 'test task',
                             'due_date' => Carbon::tomorrow()->format('Y/m/d'),
                         ]);
        $this->assertCount(1, $tasks = Task::all());
        $task = $tasks->first();

        $response = $this->actingAs($this->user)
                         ->post(route('tasks.remove', [
                             'folder' => $this->folder->id,
                             'task' => $task->id,
                         ]));
        $this->assertCount(0, Task::all());
        $response->assertRedirect(route('tasks.index', $this->folder->id));
    }
}
