<?php

namespace Modularavel\Commentable\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @method static create(array $array)
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
        'emoji',
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
        return $this->belongsTo(config('modularavel.commentable.user_model'));
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
     * Get all reactions to this comment
     *
     * @return HasMany
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    /**
     * Get grouped reactions count
     *
     * @return array
     */
    public function getReactionsSummary(): array
    {
        return $this->reactions()
            ->selectRaw('emoji, COUNT(*) as count')
            ->groupBy('emoji')
            ->toBase()
            ->get()
            ->pluck('count', 'emoji')
            ->toArray();
    }

    /**
     * Check if user has reacted with specific emoji
     *
     * @param string $emoji
     * @param int|null $userId
     * @return bool
     */
    public function hasUserReacted(string $emoji, ?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return $this->reactions()
            ->where('user_id', $userId)
           ->where('emoji', $emoji)
            ->exists();
    }

    /**
     * Scope to filter only approved comments
     *
     * @param Builder $query
     * @return void
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('is_approved', true);
    }

    /**
     * Scope to filter only parent comments (not replies)
     *
     * @param Builder $query
     * @return void
     */
    public function scopeParent(Builder $query): void
    {
        $query->whereNull('parent_id');
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
