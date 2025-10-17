<?php

namespace Modularavel\Commentable\Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Modularavel\Commentable\Livewire\CommentItem;
use Modularavel\Commentable\Models\Comment;
use Modularavel\Commentable\Tests\TestPost as Post;
use Modularavel\Commentable\Tests\TestUser as User;

/**
 * Comment Item Component Tests
 *
 * Feature tests for the CommentItem Livewire component.
 */

test('it renders the comment item component', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->assertStatus(200)
        ->assertSee('Test comment');
});

test('comment owner can start editing', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    Auth::login($user);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->call('startEdit')
        ->assertSet('isEditing', true)
        ->assertSet('editBody', 'Test comment');
});

test('non-owner cannot edit comment', function () {
    $owner = User::create([
        'name' => 'Owner',
        'email' => 'owner@example.com',
        'password' => bcrypt('password'),
    ]);

    $otherUser = User::create([
        'name' => 'Other User',
        'email' => 'other@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $owner->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    Auth::login($otherUser);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->call('startEdit')
        ->assertDispatched('error')
        ->assertSet('isEditing', false);
});

test('owner can update comment', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Original comment',
        'is_approved' => true,
    ]);

    Auth::login($user);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->set('editBody', 'Updated comment')
        ->call('updateComment')
        ->assertDispatched('commentUpdated')
        ->assertDispatched('success')
        ->assertSet('isEditing', false);

    expect($comment->fresh()->body)->toBe('Updated comment');
});

test('owner can cancel editing', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    Auth::login($user);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->set('isEditing', true)
        ->set('editBody', 'Changed text')
        ->call('cancelEdit')
        ->assertSet('isEditing', false)
        ->assertSet('editBody', 'Test comment');
});

test('owner can delete comment', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    Auth::login($user);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->call('deleteComment')
        ->assertDispatched('commentDeleted')
        ->assertDispatched('success');

    expect(Comment::count())->toBe(0)
        ->and(Comment::withTrashed()->count())->toBe(1);
});

test('non-owner cannot delete comment', function () {
    $owner = User::create([
        'name' => 'Owner',
        'email' => 'owner@example.com',
        'password' => bcrypt('password'),
    ]);

    $otherUser = User::create([
        'name' => 'Other User',
        'email' => 'other@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $owner->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    Auth::login($otherUser);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->call('deleteComment')
        ->assertDispatched('error');

    expect(Comment::count())->toBe(1);
});

test('authenticated user can toggle reply form', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    Auth::login($user);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->call('toggleReply')
        ->assertSet('isReplying', true)
        ->call('toggleReply')
        ->assertSet('isReplying', false);
});

test('guest cannot toggle reply form', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->call('toggleReply')
        ->assertDispatched('error')
        ->assertSet('isReplying', false);
});

test('authenticated user can add reply', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $parentComment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Parent comment',
        'is_approved' => true,
    ]);

    Auth::login($user);

    Livewire::test(CommentItem::class, ['comment' => $parentComment])
        ->set('replyBody', 'This is a reply')
        ->call('addReply')
        ->assertSet('replyBody', '')
        ->assertSet('isReplying', false)
        ->assertDispatched('commentAdded')
        ->assertDispatched('success');

    expect(Comment::count())->toBe(2)
        ->and(Comment::query()->latest('id')->value('body'))->toBe('This is a reply')
        ->and(Comment::query()->latest('id')->value('parent_id'))->toBe($parentComment->id);
});

test('reply validation requires minimum length', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    Auth::login($user);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->set('replyBody', 'Hi')
        ->call('addReply')
        ->assertHasErrors(['replyBody']);

    expect(Comment::count())->toBe(1);
});

test('edit validation requires minimum length', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Original comment',
        'is_approved' => true,
    ]);

    Auth::login($user);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->set('editBody', 'Hi')
        ->call('updateComment')
        ->assertHasErrors(['editBody']);

    expect($comment->fresh()->body)->toBe('Original comment');
});
