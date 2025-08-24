<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Task;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(), // automatically create a user
            'title' => $this->faker->sentence(),
            'is_completed' => false,
            'category' => $this->faker->randomElement(['Work', 'Personal', 'Other']),
            'start' => $this->faker->date(),
            'due_date' => $this->faker->date(),
        ];
    }
}
