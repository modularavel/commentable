<?php

namespace Modularavel\Commentable\Livewire;

use Modularavel\Commentable\Models\Comment;
use Modularavel\Commentable\Models\CommentReaction;
use Livewire\Component;

/**
 * Comment Item Component
 *
 * Livewire component for displaying and managing individual comments.
 * Handles editing, deleting, and replying to comments.
 */
class CommentItem extends Component
{
    /** @var Comment The comment instance */
    public Comment $comment;

    /** @var bool Whether the comment is in edit mode */
    public bool $isEditing = false;

    /** @var bool Whether the reply form is visible */
    public bool $isReplying = false;

    /** @var string The edited comment body */
    public string $editBody = '';

    /** @var string The reply body */
    public string $replyBody = '';

    /** @var array Available emoji reactions */
    public array $availableEmojis = ['👍', '❤️', '😂', '😮', '😢', '🎉'];

    /** @var array Reactions summary */
    public array $reactionsSummary = [];

    /**
     * Initialize the component
     *
     * @param Comment $comment The comment to display
     */
    public function mount(Comment $comment): void
    {
        $this->comment = $comment;
        $this->editBody = $comment->body;
        $this->loadReactions();
    }

    /**
     * Load reactions summary
     */
    public function loadReactions(): void
    {
        $this->reactionsSummary = $this->comment->getReactionsSummary();
    }

    /**
     * Enter edit mode for the comment
     *
     * Checks permissions before allowing edit
     */
    public function startEdit(): void
    {
        if (!$this->comment->canEdit()) {
            $this->dispatch('error', 'You cannot edit this comment.');
            return;
        }

        $this->isEditing = true;
        $this->editBody = $this->comment->body;
    }

    /**
     * Cancel edit mode and restore original comment body
     */
    public function cancelEdit(): void
    {
        $this->isEditing = false;
        $this->editBody = $this->comment->body;
    }

    /**
     * Update the comment with edited content
     *
     * Validates permissions and input before saving
     */
    public function updateComment(): void
    {
        // Check edit permissions
        if (!$this->comment->canEdit()) {
            $this->dispatch('error', 'You cannot edit this comment.');
            return;
        }

        // Validate the edited comment
        $this->validate([
            'editBody' => ['required', 'string', 'min:3', 'max:' . config('comments.max_length', 1000)],
        ]);

        // Update the comment
        $this->comment->update([
            'body' => $this->editBody,
        ]);

        // Exit edit mode and notify
        $this->isEditing = false;
        $this->dispatch('commentUpdated');
        $this->dispatch('success', 'Comment updated successfully!');
    }

    /**
     * Delete the comment
     *
     * Soft deletes the comment after permission check
     */
    public function deleteComment(): void
    {
        // Check delete permissions
        if (!$this->comment->canDelete()) {
            $this->dispatch('error', 'You cannot delete this comment.');
            return;
        }

        // Soft delete the comment
        $this->comment->delete();
        $this->dispatch('commentDeleted');
        $this->dispatch('success', 'Comment deleted successfully!');
    }

    /**
     * Toggle the reply form visibility
     *
     * Checks authentication before showing reply form
     */
    public function toggleReply(): void
    {
        if (!auth()->check()) {
            $this->dispatch('error', 'You must be logged in to reply.');
            return;
        }

        $this->isReplying = !$this->isReplying;
        if (!$this->isReplying) {
            $this->replyBody = '';
        }
    }

    /**
     * Add a reply to this comment
     *
     * Creates a child comment linked to this parent comment
     */
    public function addReply(): void
    {
        // Check authentication
        if (!auth()->check()) {
            $this->dispatch('error', 'You must be logged in to reply.');
            return;
        }

        // Validate the reply
        $this->validate([
            'replyBody' => ['required', 'string', 'min:3', 'max:' . config('comments.max_length', 1000)],
        ]);

        // Create the reply as a child comment
        Comment::create([
            'user_id' => auth()->id(),
            'commentable_type' => $this->comment->commentable_type,
            'commentable_id' => $this->comment->commentable_id,
            'parent_id' => $this->comment->id,
            'body' => $this->replyBody,
            'is_approved' => config('comments.auto_approve', true),
        ]);

        // Reset reply form and notify
        $this->replyBody = '';
        $this->isReplying = false;
        $this->dispatch('commentAdded');
        $this->dispatch('success', 'Reply added successfully!');
    }

    /**
     * Toggle reaction on comment
     *
     * @param string $emoji
     */
    public function toggleReaction(string $emoji): void
    {
        if (!auth()->check()) {
            $this->dispatch('error', 'You must be logged in to react.');
            return;
        }

        $userId = auth()->id();
        $existingReaction = CommentReaction::where('comment_id', $this->comment->id)
            ->where('user_id', $userId)
            ->where('emoji', $emoji)
            ->first();

        if ($existingReaction) {
            $existingReaction->delete();
        } else {
            CommentReaction::create([
                'comment_id' => $this->comment->id,
                'user_id' => $userId,
                'emoji' => $emoji,
            ]);
        }

        $this->loadReactions();
        $this->dispatch('reactionToggled');
    }

    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('comments::livewire.comment-item');
    }
}
