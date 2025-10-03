<?php

namespace Modularavel\Commentable\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modularavel\Commentable\Models\Comment;

/**
 * HasModularavelCommentable Trait
 *
 * Add this trait to any model that should have comments.
 * Provides methods to manage comments and replies on the model.
 *
 * @example
 * class Post extends Model
 * {
 *     use HasModularavelCommentable;
 * }
 */
trait HasModularavelCommentable
{
    /**
     * Get all parent comments (not replies) for this model
     *
     * Returns comments ordered by most recent first, with user and replies eager loaded
     *
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->whereNull('parent_id')
            ->with('user', 'replies')
            ->latest();
    }

    /**
     * Get all comments including replies for this model
     *
     * @return MorphMany
     */
    public function allComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get only approved parent comments for this model
     *
     * @return MorphMany
     */
    public function approvedComments(): MorphMany
    {
        return $this->comments()->where('is_approved', true);
    }

    /**
     * Get the total count of all comments (including replies)
     *
     * @return int
     */
    public function commentsCount(): int
    {
        return $this->allComments()->count();
    }
}
