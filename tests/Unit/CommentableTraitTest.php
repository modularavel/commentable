<?php

namespace Modularavel\Commentable\Tests\Unit;

use Modularavel\Commentable\Models\Comment;
use Modularavel\Commentable\Tests\TestPost as Post;
use Modularavel\Commentable\Tests\TestUser as User;

/**
 * HasModularavelCommentable Trait Tests
 *
 * Tests for the HasModularavelCommentable trait functionality when applied to models.
 */

test('it can get comments relationship', function () {
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
        'body' => 'First comment.',
        'is_approved' => true,
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Second comment.',
        'is_approved' => true,
    ]);

    $comments = $post->comments;

    expect($comments)->toHaveCount(2)
        ->and($comments->first())->toBeInstanceOf(Comment::class);
});

test('it only returns parent comments', function () {
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
        'body' => 'Parent comment.',
        'is_approved' => true,
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'parent_id' => $parentComment->id,
        'body' => 'Reply comment.',
        'is_approved' => true,
    ]);

    $comments = $post->comments;

    expect($comments)->toHaveCount(1)
        ->and($comments->first()->body)->toBe('Parent comment.');
});

test('it can get all comments including replies', function () {
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
        'body' => 'Parent comment.',
        'is_approved' => true,
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'parent_id' => $parentComment->id,
        'body' => 'Reply comment.',
        'is_approved' => true,
    ]);

    $allComments = $post->allComments;

    expect($allComments)->toHaveCount(2);
});

test('it can get only approved comments', function () {
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
        'body' => 'Approved comment.',
        'is_approved' => true,
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'body' => 'Unapproved comment.',
        'is_approved' => false,
    ]);

    $approvedComments = $post->approvedComments;

    expect($approvedComments)->toHaveCount(1)
        ->and($approvedComments->first()->body)->toBe('Approved comment.');
});

test('it can count all comments', function () {
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
        'body' => 'Parent comment.',
        'is_approved' => true,
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'parent_id' => $parentComment->id,
        'body' => 'Reply comment.',
        'is_approved' => true,
    ]);

    expect($post->commentsCount())->toBe(2);
});

test('it eager loads user and replies relationships', function () {
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
        'body' => 'Parent comment.',
        'is_approved' => true,
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'parent_id' => $parentComment->id,
        'body' => 'Reply comment.',
        'is_approved' => true,
    ]);

    $comments = $post->comments;

    // Check that user relationship is loaded
    expect($comments->first()->relationLoaded('user'))->toBeTrue()
        ->and($comments->first()->relationLoaded('replies'))->toBeTrue()
        ->and($comments->first()->user->name)->toBe('Test User')
        ->and($comments->first()->replies)->toHaveCount(1);
});

test('multiple models can have comments', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post1 = Post::create([
        'title' => 'First Post',
        'content' => 'Content of first post.',
    ]);

    $post2 = Post::create([
        'title' => 'Second Post',
        'content' => 'Content of second post.',
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post1->id,
        'body' => 'Comment on first post.',
        'is_approved' => true,
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => Post::class,
        'commentable_id' => $post2->id,
        'body' => 'Comment on second post.',
        'is_approved' => true,
    ]);

    expect($post1->comments)->toHaveCount(1)
        ->and($post2->comments)->toHaveCount(1)
        ->and($post1->comments->first()->body)->toBe('Comment on first post.')
        ->and($post2->comments->first()->body)->toBe('Comment on second post.');
});
