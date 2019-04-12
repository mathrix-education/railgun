<?php

namespace App\Notifications;

use App\Mails\PasswordResetMail;
use App\Models\User;

/**
 * Class PasswordResetNotification.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class PasswordResetNotification extends MailNotification
{
    /** @var User $user */
    public $user;


    /**
     * PasswordResetNotification constructor.
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
     * @return PasswordResetMail
     */
    public function toMail()
    {
        return (new PasswordResetMail($this->user))
            ->to($this->user->email);
    }
}
