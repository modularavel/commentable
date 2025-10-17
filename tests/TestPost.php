<?php

namespace Modularavel\Commentable\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modularavel\Commentable\Database\Factories\TestPostFactory;
use Modularavel\Commentable\Traits\Commentable;

class TestPost extends Model
{
    /** @use HasFactory<TestPostFactory> */
    use HasFactory, Commentable;

    protected $table = 'posts';

    protected $guarded = [];
}
