<?php

namespace Modularavel\Commentable;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modularavel\Commentable\Livewire\CommentThread;
use Modularavel\Commentable\Livewire\CommentItem;

/**
 * Service Provider for HasModularavelCommentable Package
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
            __DIR__ . '/../config/modularavel/commentable.php',
            'commentable'
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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'commentable');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'modularavel');

        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');

        // Publish configuration, migrations, views, and assets when in console
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/modularavel/commentable.php' => config_path('modularavel/commentable.php'),
            ], 'commentable-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'commentable-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/modularavel/commentable'),
            ], 'commentable-views');

            $this->publishes([
                __DIR__ . '/../resources/css/commentable.css' => public_path('css/modularavel/commentable.css'),
            ], 'commentable-assets');

            $this->publishes([
                __DIR__ . '/../resources/lang' => lang_path('vendor/modularavel/commentable'),
            ], 'commentable-lang');
        }

        // Register Livewire components
        Livewire::component('comment-thread', CommentThread::class);
        Livewire::component('comment-item', CommentItem::class);
    }
}
