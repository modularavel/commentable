<div class="comment-item">
    <div class="comment-header">
        <div class="comment-author">
            <span class="author-name">{{ $comment->user->name }}</span>
            <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
            @if($comment->created_at != $comment->updated_at)
                <span class="comment-edited">(edited)</span>
            @endif
        </div>
        @auth
            @if($comment->canEdit())
                <div class="comment-actions">
                    @if(!$isEditing)
                        <button wire:click="startEdit" class="btn-action">Edit</button>
                    @endif
                    <button wire:click="deleteComment" class="btn-action btn-danger" onclick="return confirm('Are you sure you want to delete this comment?')">
                        Delete
                    </button>
                </div>
            @endif
        @endauth
    </div>

    <div class="comment-body">
        @if($isEditing)
            <form wire:submit.prevent="updateComment" class="comment-edit-form">
                <div class="editor-toolbar">
                    <button type="button" class="editor-btn" onclick="document.execCommand('bold', false, null);" title="Bold">
                        <strong>B</strong>
                    </button>
                    <button type="button" class="editor-btn" onclick="document.execCommand('italic', false, null);" title="Italic">
                        <em>I</em>
                    </button>
                    <button type="button" class="editor-btn" onclick="document.execCommand('underline', false, null);" title="Underline">
                        <u>U</u>
                    </button>
                </div>
                <textarea
                    wire:model="editBody"
                    rows="3"
                    class="form-textarea"
                ></textarea>
                @error('editBody')
                    <span class="error-message">{{ $message }}</span>
                @enderror
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    <button type="button" wire:click="cancelEdit" class="btn btn-secondary btn-sm">Cancel</button>
                </div>
            </form>
        @else
            <p class="comment-text">{!! nl2br(e($comment->body)) !!}</p>
        @endif
    </div>

    @if(!$isEditing)
        <div class="comment-reactions">
            <div class="reactions-list">
                @foreach($availableEmojis as $emoji)
                    @php
                        $count = $reactionsSummary[$emoji] ?? 0;
                        $hasReacted = auth()->check() && $comment->hasUserReacted($emoji);
                    @endphp
                    <button
                        wire:click="toggleReaction('{{ $emoji }}')"
                        class="reaction-btn {{ $hasReacted ? 'active' : '' }}"
                        title="{{ $emoji }}"
                    >
                        <span class="reaction-emoji">{{ $emoji }}</span>
                        @if($count > 0)
                            <span class="reaction-count">{{ $count }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <div class="comment-footer">
        @auth
            @if(!$isEditing)
                <button wire:click="toggleReply" class="btn-reply">
                    {{ $isReplying ? 'Cancel' : 'Reply' }}
                </button>
            @endif
        @endauth
        @if($comment->replies->count() > 0)
            <span class="replies-count">{{ $comment->replies->count() }} {{ Str::plural('reply', $comment->replies->count()) }}</span>
        @endif
    </div>

    @if($isReplying)
        <div class="reply-form-container">
            <form wire:submit.prevent="addReply" class="reply-form">
                <div class="editor-toolbar">
                    <button type="button" class="editor-btn" onclick="document.execCommand('bold', false, null);" title="Bold">
                        <strong>B</strong>
                    </button>
                    <button type="button" class="editor-btn" onclick="document.execCommand('italic', false, null);" title="Italic">
                        <em>I</em>
                    </button>
                    <button type="button" class="editor-btn" onclick="document.execCommand('underline', false, null);" title="Underline">
                        <u>U</u>
                    </button>
                </div>
                <textarea
                    wire:model="replyBody"
                    rows="2"
                    class="form-textarea"
                    placeholder="Write your reply..."
                ></textarea>
                @error('replyBody')
                    <span class="error-message">{{ $message }}</span>
                @enderror
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-sm">Post Reply</button>
                </div>
            </form>
        </div>
    @endif

    @if($comment->replies->count() > 0)
        <div class="comment-replies">
            @foreach($comment->replies as $reply)
                <livewire:comment-item :comment="$reply" :key="'comment-'.$reply->id" />
            @endforeach
        </div>
    @endif
</div>
