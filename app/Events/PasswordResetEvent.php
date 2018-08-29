<?php

namespace App\Events;

use App\Models\User;

/**
 * Class PasswordResetEvent
 * Fired when a user successfully reset his password.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.1
 *
 * @copyright Mathrix Education SA
 */
class PasswordResetEvent extends BaseEvent
{
    /** @var User */
    public $user;

    /**
     * PasswordResetEvent constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
