<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Mathrix\Lumen\Bases\BaseNotification;

/**
 * Class CommentAnswerNotification.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
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
     * @param User $user
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
        // Unset unwanted relations
        unset($this->comment->user);
        unset($this->comment->parent);

        return [
            "comment" => $this->comment->toArray(),
            "user" => $this->user->makeHidden(["email"])->toArray()
        ];
    }
}
