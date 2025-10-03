<?php

use Modularavel\Commentable\Models\Comment;
use Modularavel\Commentable\Models\CommentReaction;
use Modularavel\Commentable\Tests\TestPost;
use Modularavel\Commentable\Tests\TestUser;

test('user can add reaction to comment', function () {
    $user = TestUser::query()->create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::query()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::query()->create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'This is a test comment',
        'is_approved' => true,
    ]);

    $reaction = $comment->reactions()->create([
        'user_id' => $user->id,
        'emoji' => '👍',
    ]);

    expect($reaction)->toBeInstanceOf(CommentReaction::class)
        ->and($reaction->emoji)->toBe('👍')
        ->and($reaction->comment_id)->toBe($comment->id)
        ->and($reaction->user_id)->toBe($user->id);
});

test('comment can have multiple reactions', function () {
    $user1 = TestUser::query()->create([
        'name' => 'Test User 1',
        'email' => 'user1@example.com',
        'password' => bcrypt('password')
    ]);

    $user2 = TestUser::query()->create([
        'name' => 'Test User 2',
        'email' => 'user2@example.com',
        'password' => bcrypt('password')
    ]);

    $post = TestPost::query()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post.'
    ]);

    $comment = Comment::query()->create([
        'user_id' => $user1->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'This is a test comment',
        'is_approved' => true,
    ]);

    $comment->reactions()->create([
        'user_id' => $user1->id,
        'emoji' => '👍',
    ]);

    $comment->reactions()->create([
        'user_id' => $user2->id,
        'emoji' => '❤️',
    ]);

    expect($comment->reactions()->count())->toBe(2);
});

test('get reactions summary for comment', function () {
    $user1 = TestUser::query()->create([
        'name' => 'Test User 1',
        'email' => 'user@example.com',
        'password' => bcrypt('password')
    ]);

    $user2 = TestUser::query()->create([
        'name' => 'Test User 2',
        'email' => 'user2@example.com',
        'password' => bcrypt('password')
    ]);

    $user3 = TestUser::query()->create([
        'name' => 'Test User 3',
        'email' => 'user3@example.com',
        'password' => bcrypt('password')
    ]);

    $post = TestPost::query()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post.'
    ]);

    $comment = Comment::query()->create([
        'user_id' => $user1->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'This is a test comment',
        'is_approved' => true,
    ]);

    $comment->reactions()->create([
        'user_id' => $user1->id,
        'emoji' => '👍',
    ]);

    $comment->reactions()->create([
        'user_id' => $user2->id,
        'emoji' => '👍',
    ]);

    $comment->reactions()->create([
        'user_id' => $user3->id,
        'emoji' => '❤️',
    ]);

    $summary = $comment->getReactionsSummary();

    expect($summary)->toBeArray()
        ->and($summary['👍'])->toBe(2)
        ->and($summary['❤️'])->toBe(1);
});

test('check if user has reacted with specific emoji', function () {
    $user = TestUser::query()->create([
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => bcrypt('password')
    ]);

    $post = TestPost::query()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post.'
    ]);

    $comment = Comment::query()->create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'This is a test comment',
        'is_approved' => true,
    ]);

    $comment->reactions()->create([
        'user_id' => $user->id,
        'emoji' => '👍',
    ]);

    expect($comment->hasUserReacted('👍', $user->id))->toBeTrue()
        ->and($comment->hasUserReacted('❤️', $user->id))->toBeFalse();
});

test('user cannot add duplicate reaction', function () {
    $user = TestUser::query()->create([
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => bcrypt('password')
    ]);

    $post = TestPost::query()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post.'
    ]);

    $comment = Comment::query()->create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'This is a test comment',
        'is_approved' => true,
    ]);

    $comment->reactions()->create([
        'user_id' => $user->id,
        'emoji' => '👍',
    ]);

    $comment->reactions()->create([
        'user_id' => $user->id,
        'emoji' => '👍',
    ]);
})->throws(Exception::class);

test('reactions are deleted when comment is deleted', function () {
    $user = TestUser::query()->create([
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => bcrypt('password')
    ]);

    $post = TestPost::query()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post.'
    ]);

    $comment = Comment::query()->create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'This is a test comment',
        'is_approved' => true,
    ]);

    $comment->reactions()->create([
        'user_id' => $user->id,
        'emoji' => '👍',
    ]);

    expect($comment->reactions()->count())->toBe(1);

    $comment->forceDelete();

    expect(CommentReaction::query()->count())->toBe(0);
});

test('reaction belongs to user', function () {
    $user = TestUser::query()->create([
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => bcrypt('password')
    ]);
    $post = TestPost::query()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post.'
    ]);

    $comment = Comment::query()->create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'This is a test comment',
        'is_approved' => true,
    ]);

    $reaction = $comment->reactions()->create([
        'user_id' => $user->id,
        'emoji' => '👍',
    ]);

    expect($reaction->user)->toBeInstanceOf(TestUser::class)
        ->and($reaction->user->id)->toBe($user->id);
});

test('reaction belongs to comment', function () {
    $user = TestUser::query()->create([
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => bcrypt('password')
    ]);
    $post = TestPost::query()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post.'
    ]);

    $comment = Comment::query()->create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'This is a test comment',
        'is_approved' => true,
    ]);

    $reaction = $comment->reactions()->create([
        'user_id' => $user->id,
        'emoji' => '👍',
    ]);

    expect($reaction->comment)->toBeInstanceOf(Comment::class)
        ->and($reaction->comment->id)->toBe($comment->id);
});
