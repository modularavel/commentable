<div class="comments-thread">
    <div class="comments-header">
        <h3 class="comments-title">Comments ({{ $comments->total() }})</h3>
    </div>

    @auth
        @if($showForm)
            <div class="comment-form-container">
                <form wire:submit.prevent="addComment" class="comment-form">
                    <div class="form-group">
                        <label for="newComment" class="form-label">Add a comment</label>
                        <textarea
                            wire:model="newComment"
                            id="newComment"
                            rows="3"
                            class="form-textarea"
                            placeholder="Write your comment here..."
                        ></textarea>
                        @error('newComment')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            Post Comment
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @else
        <div class="auth-prompt">
            <p>Please <a href="{{ Route::has('login') ? route('login') : '/login' }}">login</a> to leave a comment.</p>
        </div>
    @endauth

    <div class="comments-list">
        @forelse($comments as $comment)
            <livewire:comment-item :comment="$comment" :key="'comment-'.$comment->id" />
        @empty
            <div class="no-comments">
                <p>No comments yet. Be the first to comment!</p>
            </div>
        @endforelse
    </div>

    @if($comments->hasPages())
        <div class="comments-pagination">
            {{ $comments->links() }}
        </div>
    @endif
</div>
