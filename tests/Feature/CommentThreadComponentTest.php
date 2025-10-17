<?php

namespace Modularavel\Commentable\Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Modularavel\Commentable\Livewire\CommentThread;
use Modularavel\Commentable\Models\Comment;
use Modularavel\Commentable\Tests\TestPost as Post;
use Modularavel\Commentable\Tests\TestUser as User;

/**
 * Comment Thread Component Tests
 *
 * Feature tests for the CommentThread Livewire component.
 */

test('it renders the comment thread component', function () {
    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    Livewire::test(CommentThread::class, ['commentable' => $post])
        ->assertStatus(200)
        ->assertSee('Comments')
        ->assertSet('commentable.id', $post->id);
});

test('authenticated user can add a comment', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    Auth::login($user);

    Livewire::test(CommentThread::class, ['commentable' => $post])
        ->set('newComment', 'This is a new comment.')
        ->call('addComment')
        ->assertDispatched('commentAdded')
        ->assertDispatched('success');

    expect(Comment::count())->toBe(1)
        ->and(Comment::first()->body)->toBe('This is a new comment.')
        ->and(Comment::first()->user_id)->toBe($user->id);
});

test('guest cannot add a comment', function () {
    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    Livewire::test(CommentThread::class, ['commentable' => $post])
        ->set('newComment', 'This is a new comment.')
        ->call('addComment')
        ->assertDispatched('error');

    expect(Comment::count())->toBe(0);
});

test('comment validation requires minimum length', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    Auth::login($user);

    Livewire::test(CommentThread::class, ['commentable' => $post])
        ->set('newComment', 'Hi')
        ->call('addComment')
        ->assertHasErrors(['newComment']);

    expect(Comment::count())->toBe(0);
});

test('comment validation enforces maximum length', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    Auth::login($user);

    $longComment = str_repeat('a', 1001); // Exceeds max length of 1000

    Livewire::test(CommentThread::class, ['commentable' => $post])
        ->set('newComment', $longComment)
        ->call('addComment')
        ->assertHasErrors(['newComment']);

    expect(Comment::count())->toBe(0);
});

test('it displays paginated comments', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    // Create 15 comments (pagination is set to 10 per page)
    for ($i = 1; $i <= 15; $i++) {
        Comment::create([
            'user_id' => $user->id,
            'commentable_type' => Post::class,
            'commentable_id' => $post->id,
            'body' => "Comment number $i",
            'is_approved' => true,
        ]);
    }

    $component = Livewire::test(CommentThread::class, ['commentable' => $post]);

    // Should show 10 comments on the first page
    $comments = $component->viewData('comments');
    expect($comments->count())->toBe(10);
});

test('it only displays approved comments', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Approved comment',
        'is_approved' => true,
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Unapproved comment',
        'is_approved' => false,
    ]);

    $component = Livewire::test(CommentThread::class, ['commentable' => $post]);
    $comments = $component->viewData('comments');

    expect($comments->count())->toBe(1)
        ->and($comments->first()->body)->toBe('Approved comment');
});

test('it refreshes when comment events are dispatched', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $component = Livewire::test(CommentThread::class, ['commentable' => $post]);

    // Verify component has listeners for these events
    $listeners = $component->instance()->getListeners();
    expect($listeners)->toHaveKey('commentAdded', '$refresh')
        ->and($listeners)->toHaveKey('commentDeleted', '$refresh')
        ->and($listeners)->toHaveKey('commentUpdated', '$refresh');
});

test('it clears comment input after successful submission', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    Auth::login($user);

    Livewire::test(CommentThread::class, ['commentable' => $post])
        ->set('newComment', 'This is a new comment.')
        ->call('addComment')
        ->assertSet('newComment', '');
});
