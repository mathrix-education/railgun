<?php
namespace App\Events;

use App\Mails\PasswordResetMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Class PasswordResetEvent
 * Fired when a user successfully reset his password.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Events
 * @since 1.0.1
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
