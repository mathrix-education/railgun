<?php

declare(strict_types=1);

namespace Railgun\Policies;

use Railgun\Models\Comment;
use Railgun\Models\User;
use function hasScope;

class CommentPolicy extends BasePolicy
{
    public function patch(User $currentUser, Comment $comment): bool
    {
        $hasScope    = hasScope($currentUser, 'comments:read');
        $ownsComment = $currentUser->id === $comment->user_id;
        $isTooLate   = $comment->updated_at->addDays(3)->isPast();

        return $hasScope || ($ownsComment && ! $isTooLate);
    }
}
