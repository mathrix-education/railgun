<?php

namespace App\Events;

use App\Models\Comment;

/**
 * Class CommentAnswerEvent.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.2.0
 */
class CommentAnswerEvent extends BaseEvent
{
    public $comment;


    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }
}
