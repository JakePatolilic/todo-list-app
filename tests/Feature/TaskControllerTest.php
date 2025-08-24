<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase; // resets DB between tests

    /** @test */
    public function a_user_can_create_a_task()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/tasks', [
            'title' => 'My First Task',
            'category' => 'Work',
            'start' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $response->assertStatus(201)
                 ->assertJson(['title' => 'My First Task']);

        $this->assertDatabaseHas('tasks', [
            'title' => 'My First Task',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function a_user_can_view_their_tasks()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/tasks');

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => $task->title]);
    }

    /** @test */
    public function a_user_can_update_their_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson("/tasks/{$task->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['title' => 'Updated Title']);

        $this->assertDatabaseHas('tasks', ['title' => 'Updated Title']);
    }

    /** @test */
    public function a_user_can_delete_their_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson("/tasks/{$task->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
