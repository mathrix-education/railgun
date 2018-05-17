<?php
namespace App\Events;

use App\Mails\PasswordResetMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Class PasswordResetEvent
 *
 * @author Mathrix Back-End Team
 * @package App\Events
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class PasswordResetEvent extends BaseEvent
{
    /** @var User */
    public $user;


    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * @param PasswordResetEvent $event
     */
    public function handle(PasswordResetEvent $event)
    {
        Mail::to($event->user->email)
            ->send(new PasswordResetMail($event->user));
    }
}
