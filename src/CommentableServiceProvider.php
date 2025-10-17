<?php

namespace Modularavel\Commentable;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modularavel\Commentable\Livewire\CommentThread;
use Modularavel\Commentable\Livewire\CommentItem;

/**
 * Service Provider for Commentable Package
 *
 * Registers configuration, migrations, views, and Livewire components
 * for the commentable functionality.
 */
class CommentableServiceProvider extends ServiceProvider
{
    /**
     * Register package services and configuration
     *
     * Merges the package configuration with the application's config
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/comments.php',
            'comments'
        );
    }

    /**
     * Bootstrap package services
     *
     * Loads migrations and views, publishes assets, and registers Livewire components
     */
    public function boot(): void
    {
        // Load package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load package views with 'comments' namespace
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'comments');

        // Publish configuration, migrations, views, and assets when in console
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/comments.php' => config_path('comments.php'),
            ], 'comments-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'comments-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/comments'),
            ], 'comments-views');

            $this->publishes([
                __DIR__ . '/../resources/css/comments.css' => public_path('css/comments.css'),
            ], 'comments-assets');
        }

        // Register Livewire components
        Livewire::component('comment-thread', CommentThread::class);
        Livewire::component('comment-item', CommentItem::class);
    }
}
