<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use function scope;

class CommentPolicy extends BasePolicy
{
    public function patch(User $currentUser, Comment $comment) : bool
    {
        $hasScope    = scope($currentUser, 'comments:read');
        $ownsComment = $currentUser->id === $comment->user_id;
        $isTooLate   = $comment->updated_at->addDays(3)->isPast();

        return $hasScope || ($ownsComment && ! $isTooLate);
    }
}
