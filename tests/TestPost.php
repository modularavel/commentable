<?php

namespace Modularavel\Commentable\Tests;

use Illuminate\Database\Eloquent\Model;
use Modularavel\Commentable\Traits\Commentable;

class TestPost extends Model
{
    use Commentable;

    protected $table = 'posts';

    protected $guarded = [];
}
