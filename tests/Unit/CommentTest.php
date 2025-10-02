<?php

namespace Modularavel\Commentable\Tests\Unit;

use Modularavel\Commentable\Models\Comment;
use Modularavel\Commentable\Tests\TestPost;
use Modularavel\Commentable\Tests\TestUser;

/**
 * Comment Model Tests
 *
 * Tests for the Comment model functionality including relationships,
 * scopes, and permission checks.
 */

test('it can create a comment', function () {
    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'This is a test comment.',
        'is_approved' => true,
    ]);

    expect($comment->body)->toBe('This is a test comment.')
        ->and($comment->user_id)->toBe($user->id)
        ->and($comment->commentable_id)->toBe($post->id);
});

test('it belongs to a user', function () {
    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'This is a test comment.',
        'is_approved' => true,
    ]);

    expect($comment->user)->toBeInstanceOf(TestUser::class)
        ->and($comment->user->id)->toBe($user->id)
        ->and($comment->user->name)->toBe('Test User');
});

test('it belongs to a commentable entity', function () {
    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'This is a test comment.',
        'is_approved' => true,
    ]);

    expect($comment->commentable)->toBeInstanceOf(TestPost::class)
        ->and($comment->commentable->id)->toBe($post->id);
});

test('it can have replies', function () {
    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $parentComment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Parent comment.',
        'is_approved' => true,
    ]);

    $replyComment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'parent_id' => $parentComment->id,
        'body' => 'Reply to parent comment.',
        'is_approved' => true,
    ]);

    expect($parentComment->replies)->toHaveCount(1)
        ->and($parentComment->replies->first()->id)->toBe($replyComment->id)
        ->and($replyComment->parent->id)->toBe($parentComment->id);
});

test('it can scope approved comments', function () {
    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Approved comment.',
        'is_approved' => true,
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Unapproved comment.',
        'is_approved' => false,
    ]);

    $approvedComments = Comment::approved()->get();

    expect($approvedComments)->toHaveCount(1)
        ->and($approvedComments->first()->body)->toBe('Approved comment.');
});

test('it can scope parent comments', function () {
    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $parentComment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Parent comment.',
        'is_approved' => true,
    ]);

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'parent_id' => $parentComment->id,
        'body' => 'Reply comment.',
        'is_approved' => true,
    ]);

    $parentComments = Comment::query()->whereNull('parent_id')->get();

    expect($parentComments)->toHaveCount(1)
        ->and($parentComments->first()->body)->toBe('Parent comment.');
});

test('it checks if user is owner', function () {
    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $otherUser = TestUser::create([
        'name' => 'Other User',
        'email' => 'other@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment.',
        'is_approved' => true,
    ]);

    expect($comment->isOwner($user->id))->toBeTrue()
        ->and($comment->isOwner($otherUser->id))->toBeFalse();
});

test('it checks if user can edit comment', function () {
    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $otherUser = TestUser::create([
        'name' => 'Other User',
        'email' => 'other@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment.',
        'is_approved' => true,
    ]);

    expect($comment->canEdit($user->id))->toBeTrue()
        ->and($comment->canEdit($otherUser->id))->toBeFalse();
});

test('it checks if user can delete comment', function () {
    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $otherUser = TestUser::create([
        'name' => 'Other User',
        'email' => 'other@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment.',
        'is_approved' => true,
    ]);

    expect($comment->canDelete($user->id))->toBeTrue()
        ->and($comment->canDelete($otherUser->id))->toBeFalse();
});

test('it can be soft deleted', function () {
    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment.',
        'is_approved' => true,
    ]);

    $comment->delete();

    expect(Comment::count())->toBe(0)
        ->and(Comment::withTrashed()->count())->toBe(1)
        ->and($comment->fresh()->trashed())->toBeTrue();
});
