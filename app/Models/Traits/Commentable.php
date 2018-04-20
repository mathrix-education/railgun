<?php
namespace App\Models\Traits;

use App\Models\Comment;

/**
 * Trait Commentable
 *
 * @author Mathrix Back-End Team
 * @package App\Models\Traits
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
trait Commentable
{
    /**
     * Get all of the post"s comments.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, "commentable");
    }
}
