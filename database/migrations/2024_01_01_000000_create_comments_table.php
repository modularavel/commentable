<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('commentable', 'commentable_index');
            $table->foreignId('parent_id')->index()->nullable()->constrained('comments')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
            $table->softDeletes();

            //$table->index(['commentable_type', 'commentable_id']);
            //$table->index('parent_id');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('is_approved');
            $table->index('deleted_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
