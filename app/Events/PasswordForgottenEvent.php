<?php
namespace App\Events;

use App\Mails\PasswordForgottenMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Class PasswordForgottenEvent
 * Fired when a user ask for password reset.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Events
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class PasswordForgottenEvent extends BaseEvent
{
    /** @var User */
    public $user;


    /**
     * PasswordForgottenEvent constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
