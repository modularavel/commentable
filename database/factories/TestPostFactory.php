<?php

namespace Modularavel\Commentable\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modularavel\Commentable\Tests\TestPost;

class TestPostFactory extends Factory
{
    protected $model = TestPost::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ];
    }
}
