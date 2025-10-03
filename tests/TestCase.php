<?php

namespace Modularavel\Commentable\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\LivewireServiceProvider;
use Modularavel\Commentable\CommentableServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Base Test Case
 *
 * Provides the foundation for all package tests with necessary setup
 */
class TestCase extends Orchestra
{
    use RefreshDatabase;

    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Configure factory namespaces if needed
        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Modularavel\\HasModularavelCommentable\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        $this->setUpDatabase();
    }

    /**
     * Get package providers
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            CommentableServiceProvider::class,
        ];
    }

    /**
     * Define environment setup
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true, // To support SQLite foreign key constraints
        ]);

        config()->set('auth.providers.users.model', TestUser::class);

        // Setup comments configuration
        config()->set('modularavel.commentable.user_model', TestUser::class);
        config()->set('modularavel.commentable.auto_approve', true);
        config()->set('modularavel.commentable.per_page', 10);
        config()->set('modularavel.commentable.max_length', 1000);
        config()->set('modularavel.commentable.allow_nested_replies', true);
    }

    /**
     * Define database migrations
     *
     * @return void
     */
    protected function setUpDatabase(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
