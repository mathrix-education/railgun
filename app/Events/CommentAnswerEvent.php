<?php
namespace App\Events;

use App\Models\Comment;

/**
 * Class CommentAnswerEvent.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.2.0
 * @copyright Mathrix Education SA
 * @package   App\Events
 */
class CommentAnswerEvent extends BaseEvent
{
    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }
}
