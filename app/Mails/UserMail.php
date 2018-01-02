<?php
namespace App\Mails;
use App\Models\User;

/**
 * Class UserMail
 *
 * @author Mathrix Back-End Team
 * @package App\Mails
 * @since 1.0.1
 * @copyright Mathrix SA
 */
class UserMail extends BaseMail
{
    /** @var User The mail user */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;

        parent::__construct();
    }
}
