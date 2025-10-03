<?php

namespace Modularavel\Commentable\Livewire;

use Illuminate\View\View;
use Modularavel\Commentable\Models\Comment;
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
    public array $availableEmojis = [
       ':like:', ':favorite:', ':laugh:', ':wow:', ':sad:', ':angry:'
    ];

    /** @var array Reactions summary */
    public array $reactionsSummary = [];

    /**
     * Initialize the component
     *
     */
    public function mount(): void
    {
        $this->fill($this->comment);

        // Load the comment and reactions summary
        $this->editBody = $this->comment->body;

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
            $this->dispatch('error', __('You cannot edit this comment.'));
            return;
        }

        $this->isEditing = true;
        $this->editBody = $this->comment->body;

        $this->setAutofocusOnTextarea("edit-comment-{$this->comment->id}");
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
            $this->dispatch('error', __('You cannot edit this comment.'));
            return;
        }

        // Validate the edited comment
        $this->validate([
            'editBody' => ['required', 'string', 'min:3', 'max:' . config('modularavel.commentable.max_length', 1000)],
        ]);

        // Update the comment
        $this->comment->update([
            'body' => $this->editBody,
        ]);

        // Exit edit mode and notify
        $this->isEditing = false;

        $this->dispatch('commentUpdated');
        $this->dispatch('success', __('Comment updated successfully!'));
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
            $this->dispatch('error', __('You cannot delete this comment.'));
            return;
        }

        // Soft delete the comment
        $this->comment->forceDelete();

        $this->dispatch('commentDeleted', $this->comment->id);
        $this->dispatch('success', __('Comment deleted successfully!'));
    }

    /**
     * Toggle the reply form visibility
     *
     * Checks authentication before showing reply form
     */
    public function toggleReply(): void
    {
        if (!auth()->check()) {
            $this->dispatch('error', __('You must be logged in to reply.'));
            return;
        }

        $this->isReplying = !$this->isReplying;

        if (!$this->isReplying) {
            $this->replyBody = '';
        } else {
            $this->setAutofocusOnTextarea("reply-comment-{$this->comment->id}");
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
            $this->dispatch('error', __('You must be logged in to reply.'));
            return;
        }

        // Validate the reply
        $this->validate([
            'replyBody' => ['required', 'string', 'min:3', 'max:' . config('modularavel.commentable.max_length', 1000)],
        ]);

        // Create the reply as a child comment
        Comment::create([
            'user_id' => auth()->id(),
            'commentable_type' => $this->comment->commentable_type,
            'commentable_id' => $this->comment->commentable_id,
            'parent_id' => $this->comment->id,
            'body' => $this->replyBody,
            'is_approved' => config('modularavel.commentable.auto_approve', true),
        ]);

        // Reset reply form and notify
        $this->replyBody = '';
        $this->isReplying = false;

        $this->dispatch('commentAdded');
        $this->dispatch('success', __('Reply added successfully!'));
    }

    private function setAutofocusOnTextarea(string $elementId) : void
    {
        $this->js("
            setTimeout(() => {
                const textarea = document.getElementById('$elementId');
                if (textarea) {
                    textarea.focus();
                    textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                }
            }, 50);
        ");
    }

    /**
     * Toggle reaction on comment
     *
     * @param string $emoji
     */
    public function toggleReaction(string $emoji): void
    {
        // Check if user is authenticated to react to comments
        if (!auth()->check()) {
            $this->dispatch('error', __('You must be logged in to react.'));
            return;
        }

        // Get current user ID
        $userId = auth()->id();

        // Check if the user has already reacted with this emoji
        $existingReaction = $this->comment->reactions()
            ->where('user_id', $userId)
            ->where('emoji', $emoji)
            ->first();

        if ($existingReaction) {
            $existingReaction->delete();
        } else {
            $this->comment->reactions()->create([
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
     * @return View|string
     */
    public function render(): View|string
    {
        return view('commentable::livewire.comment-item');
    }
}
