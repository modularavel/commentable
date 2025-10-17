# Laravel Comments Threads

A Laravel Blade Livewire package for threaded comments with nested replies.

## Screenshots

![Comment Thread](screenshot/01.png)

![Comment Form](screenshot/02.png)

![Nested Replies](screenshot/04.png)

![Threaded Comments with Nested Replies and Real-time Updates with Livewire Threads](screenshot/04.png)

## Installation

```bash
composer require modularavel/commentable
```

## Publish Migrations

```bash
php artisan vendor:publish --tag=commentable-migrations
```

## Publish Configuration

```bash
php artisan vendor:publish --tag=commentable-config
```

## Publish Assets

```bash
php artisan vendor:publish --tag=commentable-assets
```
## Publish Languages

```bash
php artisan vendor:publish --tag=commentable-lang
```

## Publish Blade Views

```bash
php artisan vendor:publish --tag=commentable-views
```

## Run Migrations

```bash
php artisan migrate
```

## Usage

### In Your Blade View

```blade
<livewire:comment-thread :commentable="$post" />
```

### Make Your Model Commentable

```php
use Modularavel\Commentable\Traits\HasModularavelCommentable;

class Post extends Model
{
    use HasModularavelCommentable;
}
```

## Configuration

Edit `config/modularavel/commentable.php` to customize:
- User model
- Pagination settings
- Moderation options
- Display preferences

## Features

- Nested comment replies
- Real-time updates with Livewire
- User authentication integration
- Edit and delete comments
- Customizable views
- Pagination support
- Markdown support (optional)

## License

MIT

## Test

```bash
composer test
```