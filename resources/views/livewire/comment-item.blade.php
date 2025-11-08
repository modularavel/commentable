<div class="comment-item" data-comment-id="{{ $comment->id }}">
    <div class="comment-header">
        <div class="comment-author">
            <span class="text-cyan-400 font-bold text-md">{{ $comment->user->name }}</span>
            <span class="separator text-muted mx-1 text-gray-400">•</span>
            <span class="text-[.75rem] text-gray-500 dark:text-gray-300 inline-flex items-center gap-1" wire:poll.visible.10000ms wire:ignore.self wire:target="comment.created_at">
               <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                  <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
                  <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/>
                </svg> {{ $comment->created_at->diffForHumans() }}
            </span>
            @if($comment->created_at != $comment->updated_at)
                <span class="separator text-muted mx-1 text-gray-400">•</span>
                <span class="comment-edited flex items-center gap-0.5">
                   <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                    </svg> {{ __('edited') }}
                </span>
            @endif
        </div>
        @auth
            @if($comment->canEdit())
                <div class="comment-actions">
                    @if(!$isEditing)
                        <button wire:click="startEdit" class="btn-action">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square text-amber-300" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                            </svg>
                        </button>
                    @endif
                    <button wire:click.prevent="deleteComment" wire:confirm="{{ __('Are you sure you want to delete this comment?') }}" class="btn-action btn-danger" >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill text-red-300" viewBox="0 0 16 16">
                            <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                        </svg>
                    </button>
                </div>
            @endif
        @endauth
    </div>

    <div class="comment-body">
        @if($isEditing)
            <form wire:submit.prevent="updateComment" class="comment-edit-form">
                <textarea
                        wire:model="editBody"
                        rows="2"
                        id="edit-comment-{{ $comment->id }}"
                        class="form-textarea"
                        placeholder="{{ __('Write a comment...') }}"
                        required
                        autofocus
                ></textarea>

                @error('editBody')
                <span class="error-message">{{ $message }}</span>
                @enderror

                <div class="form-actions my-2 inline-flex items-center justify-end gap-4">
                    <div class="form-actions my-2 inline-flex items-center justify-end gap-4">
                        <button type="submit" class="btn btn-primary btn-sm inline-flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                                <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0"/>
                                <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708"/>
                            </svg> {{ __('Submit') }}
                        </button>
                        <button type="button" wire:click.prevent="cancelEdit" class="btn btn-secondary btn-sm inline-flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                                <path d="M13.854 2.146a.5.5 0 0 1 0 .708l-11 11a.5.5 0 0 1-.708-.708l11-11a.5.5 0 0 1 .708 0Z"/>
                                <path d="M2.146 2.146a.5.5 0 0 0 0 .708l11 11a.5.5 0 0 0 .708-.708l-11-11a.5.5 0 0 0-.708 0Z"/>
                            </svg> {{ __('Cancel') }}
                        </button>
                    </div>
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
                    <div wire:key="reaction-{{ $comment->id }}-{{ $emoji }}">
                        @php
                            $count = $reactionsSummary[$emoji] ?? 0;
                            $hasReacted = auth()->check() && $comment->hasUserReacted($emoji, auth()->id());
                            $parsedEmoji = match($emoji) {
                                ':like:' => '👍',
                                ':favorite:' => '❤️',
                                ':laugh:' => '😂',
                                ':wow:' => '😮',
                                ':sad:' => '😢',
                                ':angry:' => '😡',
                                default => $emoji,
                            };
                        @endphp
                        <button
                                key="reaction-{{ $comment->id }}-{{ $emoji }}"
                                wire:click="toggleReaction('{{ $emoji }}')"
                                @class(['reaction-btn', 'active' => $hasReacted])
                                title="{{ $emoji }}"
                        >
                            <span class="reaction-emoji">
                                {{ $parsedEmoji }}
                            </span>
                            @if($count > 0)
                                <span class="reaction-count">{{ $count }}</span>
                            @endif
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="comment-footer inline-flex items-center justify-between">
        @if(!$isEditing && !$isReplying)
            @auth
                <button wire:click.prevent="toggleReply" class="inline-flex items-center gap-1 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-reply-fill" viewBox="0 0 16 16">
                        <path d="M5.921 11.9 1.353 8.62a.72.72 0 0 1 0-1.238L5.921 4.1A.716.716 0 0 1 7 4.719V6c1.5 0 6 0 7 8-2.5-4.5-7-4-7-4v1.281c0 .56-.606.898-1.079.62z"/>
                    </svg> {{ __('Reply') }}
                </button>
            @else
                <a href="{{ Route::has('login') ? route('login') : '/login' }}" class="inline-flex items-center gap-1 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-reply-fill" viewBox="0 0 16 16">
                        <path d="M5.921 11.9 1.353 8.62a.72.72 0 0 1 0-1.238L5.921 4.1A.716.716 0 0 1 7 4.719V6c1.5 0 6 0 7 8-2.5-4.5-7-4-7-4v1.281c0 .56-.606.898-1.079.62z"/>
                    </svg> {{ __('Reply') }}
                </a>
            @endauth
            <div @class(['replies-count', 'hidden' => $comment->replies->count() < 1])>
                <span class="replies-count-number mr-1" data-replies-count-comment-id="{{ $comment->parent_id }}">{{ $comment->replies->count() }}</span> {{ Str::plural(__('reply'), $comment->replies->count()) }}
            </div>
        @endif
    </div>

    @if($isReplying)
        <div class="reply-form-container">
            <form wire:submit.prevent="addReply" class="reply-form">
                <textarea
                        wire:model="replyBody"
                        id="reply-comment-{{ $comment->id }}"
                        rows="2"
                        class="form-textarea"
                        placeholder="{{ __('Reply to this comment...') }}"
                        required
                        autofocus
                ></textarea>
                @error('replyBody')
                <span class="error-message">{{ $message }}</span>
                @enderror
                <div class="form-actions my-2 inline-flex items-center justify-end gap-4">
                    <button type="submit" class="btn btn-primary btn-sm inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                            <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0"/>
                            <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708"/>
                        </svg> {{ __('Submit') }}
                    </button>
                    <button type="button" wire:click.prevent="toggleReply" class="btn btn-secondary btn-sm inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                            <path d="M13.854 2.146a.5.5 0 0 1 0 .708l-11 11a.5.5 0 0 1-.708-.708l11-11a.5.5 0 0 1 .708 0Z"/>
                            <path d="M2.146 2.146a.5.5 0 0 0 0 .708l11 11a.5.5 0 0 0 .708-.708l-11-11a.5.5 0 0 0-.708 0Z"/>
                        </svg> {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if($comment->replies->count() > 0)
        <div class="comment-replies">
            @foreach($comment->replies as $reply)
                <div wire:key="reply-{{ $reply->id }}">
                    <livewire:comment-item :comment="$reply" :key="'comment-'.$reply->id" />
                </div>
            @endforeach
        </div>
    @endif
</div>

