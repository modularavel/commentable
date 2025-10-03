<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model that will be used for comments.
    |
    */
    'user_model' => env('MODULARAVEL_COMMENTABLE_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Auto Approve
    |--------------------------------------------------------------------------
    |
    | Automatically approve comments when they are created.
    | Set to false if you want to manually moderate comments.
    |
    */
    'auto_approve' => env('MODULARAVEL_COMMENTABLE_AUTO_APPROVE', true),

    /*
    |--------------------------------------------------------------------------
    | Comments Per Page
    |--------------------------------------------------------------------------
    |
    | Number of comments to display per page.
    |
    */
    'per_page' => env('MODULARAVEL_COMMENTABLE_PER_PAGE', 10),

    /*
    |--------------------------------------------------------------------------
    | Maximum Comment Length
    |--------------------------------------------------------------------------
    |
    | Maximum character length for a comment.
    |
    */
    'max_length' => env('MODULARAVEL_COMMENTABLE_MAX_LENGTH', 300),

    /*
    |--------------------------------------------------------------------------
    | Allow Nested Replies
    |--------------------------------------------------------------------------
    |
    | Allow users to reply to replies.
    | Set too false to only allow one level of nesting.
    |
    */
    'allow_nested_replies' => env('MODULARAVEL_COMMENTABLE_ALLOW_NESTED_REPLIES', true),

    /*
    |--------------------------------------------------------------------------
    | Date Format
    |--------------------------------------------------------------------------
    |
    | Date format for displaying comment timestamps.
    |
    */
    'date_format' => env('MODULARAVEL_COMMENTABLE_DATE_FORMAT', 'M d, Y'),

    /*
    |--------------------------------------------------------------------------
    | Guest Commenting
    |--------------------------------------------------------------------------
    |
    | Allow guests to comment without logging in.
    |
    */
    'allow_guest_comments' => env('MODULARAVEL_COMMENTABLE_ALLOW_GUEST_COMMENTS', false),
];
