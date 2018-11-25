<?php
namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;

/**
 * Class CommentAnswerNotification.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since
 * @copyright Mathrix Education SA
 */
class CommentAnswerNotification extends BaseNotification
{
    /** @var Comment $comment */
    public $comment;
    /** @var User $user */
    public $user;

    /**
     * CommentAnswerNotification constructor.
     *
     * @param User    $user
     * @param Comment $comment
     */
    public function __construct(Comment $comment, User $user)
    {
        $this->comment = $comment;
        $this->user = $user;
    }

    public function via(): array
    {
        return ["database"];
    }

    public function toDatabase(): array
    {
        return [
            "comment_id" => $this->comment->id,
            "user_id" => $this->user->id
        ];
    }
}
