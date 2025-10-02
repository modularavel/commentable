<?php

namespace Modularavel\Commentable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Comment Model
 *
 * Represents a comment on any commentable entity.
 * Supports threaded comments with parent-child relationships.
 *
 * @property int $id
 * @property int $user_id
 * @property string $commentable_type
 * @property int $commentable_id
 * @property int|null $parent_id
 * @property string $body
 * @property bool $is_approved
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Comment extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'commentable_type',
        'commentable_id',
        'parent_id',
        'body',
        'is_approved',
    ];

    /**
     * The attributes that should be cast
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /**
     * Get the user who created the comment
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('comments.user_model'));
    }

    /**
     * Get the commentable entity (polymorphic relationship)
     *
     * @return MorphTo
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent comment (for replies)
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get all replies to this comment
     *
     * @return HasMany
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->with('user', 'replies')
            ->latest();
    }

    /**
     * Scope to filter only approved comments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope to filter only parent comments (not replies)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParent($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Check if the given user is the owner of this comment
     *
     * @param int|null $userId User ID to check (defaults to authenticated user)
     * @return bool
     */
    public function isOwner(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        return $this->user_id === $userId;
    }

    /**
     * Check if the given user can edit this comment
     *
     * @param int|null $userId User ID to check (defaults to authenticated user)
     * @return bool
     */
    public function canEdit(?int $userId = null): bool
    {
        return $this->isOwner($userId);
    }

    /**
     * Check if the given user can delete this comment
     *
     * @param int|null $userId User ID to check (defaults to authenticated user)
     * @return bool
     */
    public function canDelete(?int $userId = null): bool
    {
        return $this->isOwner($userId);
    }
}
