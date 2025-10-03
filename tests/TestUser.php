<?php

namespace Modularavel\Commentable\Tests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modularavel\Commentable\Database\Factories\TestUserFactory;

class TestUser extends Authenticatable
{
    /** @use HasFactory<TestUserFactory> */
    use HasFactory;

    protected $table = 'users';

    protected $guarded = [];
}
