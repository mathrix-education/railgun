<?php
namespace App\Events;

use App\Models\Comment;

/**
 * Class CommentAnswerEvent.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.1.3
 * @copyright Mathrix Education SA
 */
class CommentAnswerEvent extends BaseEvent
{
    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }
}
