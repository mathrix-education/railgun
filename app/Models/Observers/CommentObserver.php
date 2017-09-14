<?php

namespace App\Models\Observers;

use App\Models\Comment;
use Mathrix\Lumen\Observers\BaseObserver;

/**
 * Class CommentObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class CommentObserver extends BaseObserver
{
    /**
     * @param Comment $comment
     *
     * @return bool
     */
    public function saving($comment)
    {
        return parent::saving($comment);
    }
}