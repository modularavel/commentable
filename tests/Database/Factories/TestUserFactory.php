<?php

namespace Modularavel\Commentable\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modularavel\Commentable\Tests\TestUser;

class TestUserFactory extends Factory
{
    protected $model = TestUser::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
