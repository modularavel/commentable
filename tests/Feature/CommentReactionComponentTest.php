<?php

use Livewire\Livewire;
use Modularavel\Commentable\Livewire\CommentItem;
use Modularavel\Commentable\Models\Comment;
use Modularavel\Commentable\Models\CommentReaction;
use Modularavel\Commentable\Tests\TestPost;
use Modularavel\Commentable\Tests\TestUser;
use function Pest\Laravel\actingAs;

test('authenticated user can toggle reaction on comment', function () {
    $user = TestUser::query()->create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::query()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    actingAs($user);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->call('toggleReaction', ':like:')
        ->assertDispatched('reactionToggled');

    expect(CommentReaction::count())->toBe(1)
        ->and(CommentReaction::first()->emoji)->toBe(':like:');
});

test('toggling reaction twice removes it', function () {
    $user = TestUser::query()->create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::query()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    actingAs($user);

    $component = Livewire::test(CommentItem::class, ['comment' => $comment])
        ->call('toggleReaction', ':like:');

    expect(CommentReaction::count())->toBe(1);

    $component->call('toggleReaction', ':like:');

    expect(CommentReaction::count())->toBe(0);
});

test('guest cannot add reaction', function () {
    $user = TestUser::query()->create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::query()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->call('toggleReaction', ':like:')
        ->assertDispatched('error', 'You must be logged in to react.');

    expect(CommentReaction::count())->toBe(0);
});

test('reactions summary is loaded on mount', function () {
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
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user1->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    CommentReaction::create([
        'comment_id' => $comment->id,
        'user_id' => $user1->id,
        'emoji' => ':like:',
    ]);

    CommentReaction::create([
        'comment_id' => $comment->id,
        'user_id' => $user2->id,
        'emoji' => ':like:',
    ]);

    actingAs($user1);

    $component = Livewire::test(CommentItem::class, ['comment' => $comment]);

    expect($component->get('reactionsSummary'))->toBeArray()
        ->and($component->get('reactionsSummary')[':like:'])->toBe(2);
});

test('multiple users can react with different emojis', function () {
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
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user1->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    actingAs($user1);

    Livewire::test(CommentItem::class, ['comment' => $comment])
        ->call('toggleReaction', ':like:');

    actingAs($user2);

    Livewire::test(CommentItem::class, ['comment' => $comment->fresh()])
        ->call('toggleReaction', ':favorite:');

    expect(CommentReaction::count())->toBe(2)
        ->and($comment->fresh()->getReactionsSummary()[':like:'])->toBe(1)
        ->and($comment->fresh()->getReactionsSummary()[':favorite:'])->toBe(1);
});

test('available emojis are set on component', function () {
    $user = TestUser::query()->create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => bcrypt('password'),
    ]);

    $post = TestPost::query()->create([
        'title' => 'Test Post',
        'content' => 'This is a test post.',
    ]);

    $comment = Comment::create([
        'user_id' => $user->id,
        'commentable_type' => TestPost::class,
        'commentable_id' => $post->id,
        'body' => 'Test comment',
        'is_approved' => true,
    ]);

    actingAs($user);

    $component = Livewire::test(CommentItem::class, ['comment' => $comment]);

    expect($component->get('availableEmojis'))->toBeArray()
        ->and($component->get('availableEmojis'))->toContain(':like:', ':favorite:', ':laugh:', ':wow:', ':sad:', ':angry:');
});
