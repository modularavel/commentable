<div class="comments-thread">
    <div class="comments-header">
        <h3 class="comments-title">{{ __('Comments') }} ({{ $comments->total() }})</h3>
    </div>

    @auth
        @if($showForm)
            <div class="comment-form-container">
                <form wire:submit.prevent="addComment" class="comment-form">
                    <div class="form-group">
                        <label for="newComment" class="form-label mb-1">{{ __('Add a comment') }}</label>
                        <textarea
                            wire:model="newComment"
                            id="newComment"
                            rows="3"
                            class="form-textarea mb-2"
                            placeholder="{{ __('Write a comment...') }}"
                        ></textarea>
                        @error('newComment')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-actions inline-flex justify-end">
                        <button type="submit" class="btn btn-primary inline-flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                                <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0"/>
                                <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708"/>
                            </svg> {{ __('Post Comment') }}
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
            <div wire:key="comment-list-item-{{ $comment->id }}">
                <livewire:comment-item :comment="$comment" :key="'comment-'.$comment->id" />
            </div>
        @empty
            <div class="no-comments">
                <p>{{ __('No comments yet. Be the first to comment!') }}</p>
            </div>
        @endforelse
    </div>

    @if($comments->hasPages())
        <div class="comments-pagination">
            {{ $comments->links() }}
        </div>
    @endif
</div>

@script
<script>
    Livewire.on('commentDeleted', (commentId) => {
        setTimeout(() => {
            document.querySelector(`[data-comment-id="${commentId}"]`)?.classList.add('hidden');

            const repliesCount = document.querySelector(`[data-comment-id="${commentId}"]`).closest('.comment-item').querySelector('.replies-count-number');

            if (repliesCount) {
                const parentCommentId = parseInt(repliesCount.attributes['data-replies-count-comment-id']?.value);

                const parentComment = document.querySelector(`[data-comment-id="${parentCommentId}"]`);

                if (parentComment) {
                    let repliesCountElement = parentComment.querySelector('.replies-count-number')

                    const repliesCountNumber = parseInt(repliesCountElement.textContent);

                    if (repliesCountNumber > 1) {
                        repliesCountElement.textContent = repliesCountNumber - 1;
                    } else {
                        repliesCountElement.parentElement.classList.add('hidden');
                    }
                }
            }

        }, 50);
    });
</script>
@endscript
