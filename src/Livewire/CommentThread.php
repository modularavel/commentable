<?php

namespace Modularavel\Commentable\Livewire;

use Illuminate\Database\Eloquent\Model;
use Modularavel\Commentable\Models\Comment;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Comment Thread Component
 *
 * Main Livewire component for displaying and managing a thread of comments.
 * Handles adding new comments and pagination.
 */
class CommentThread extends Component
{
    use WithPagination;

    /** @var Model The commentable model instance */
    public Model $commentable;

    /** @var string The new comment input */
    public string $newComment = '';

    /** @var bool Whether to show the comment form */
    public bool $showForm = true;

    /**
     * Event listeners for Livewire
     *
     * @var array<string, string>
     */
    protected $listeners = [
        'commentAdded' => '$refresh',
        'commentUpdated' => '$refresh',
        'commentDeleted' => '$refresh',
    ];

    public function getListeners()
    {
        return array_merge($this->listeners, parent::getListeners());
    }

    /**
     * Validation rules for the component
     *
     * @return array<string, array<string>>
     */
    public function rules()
    {
        return [
            'newComment' => ['required', 'string', 'min:3', 'max:' . config('modularavel.commentable.max_length', 1000)],
        ];
    }

    /**
     * Initialize the component
     *
     */
    public function mount(): void
    {
        $this->fill($this->commentable);
    }

    /**
     * Add a new comment to the thread
     *
     * Validates user authentication, creates the comment, and dispatches events
     */
    public function addComment(): void
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            $this->dispatch('error', 'You must be logged in to comment.');
            return;
        }

        // Validate the comment input
        $this->validate();

        // Create the new comment
        Comment::create([
            'user_id' => auth()->id(),
            'commentable_type' => get_class($this->commentable),
            'commentable_id' => $this->commentable->id,
            'body' => $this->newComment,
            'is_approved' => config('modularavel.commentable.auto_approve', true),
        ]);

        // Reset the form and notify
        $this->newComment = '';
        $this->dispatch('commentAdded');
        $this->dispatch('success', 'Comment added successfully!');
    }

    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Get paginated approved comments
        $comments = $this->commentable
            ->comments()
            ->approved()
            ->paginate(config('modularavel.commentable.per_page', 10));

        return view('commentable::livewire.comment-thread', [
            'comments' => $comments,
        ]);
    }
}
