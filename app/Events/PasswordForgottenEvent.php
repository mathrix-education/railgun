<?php

namespace App\Events;

use App\Models\User;

/**
 * Class PasswordForgottenEvent
 * Fired when a user ask for password reset.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.1
 *
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
