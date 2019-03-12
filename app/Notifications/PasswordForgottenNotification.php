<?php

namespace App\Notifications;

use App\Mails\PasswordForgottenMail;
use App\Models\User;

/**
 * Class PasswordForgottenNotification.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Notifications
 */
class PasswordForgottenNotification extends MailNotification
{
    /** @var User $user */
    public $user;


    /**
     * PasswordForgottenNotification constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * Get the mail representation of the notification.
     *
     * @return PasswordForgottenMail
     */
    public function toMail()
    {
        return (new PasswordForgottenMail($this->user))
            ->to($this->user->email);
    }
}
