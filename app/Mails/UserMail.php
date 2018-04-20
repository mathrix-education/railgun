<?php
namespace App\Mails;

use App\Models\User;

/**
 * Class UserMails
 * Bae class for all mails which require an user passed through
 *
 * @author Mathrix Back-End Team
 * @package App\Mails
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
abstract class UserMail extends BaseMail
{
    /** @var User The mail user */
    protected $user;


    /**
     * UserMail constructor.
     *
     * @param User $user the mail user
     */
    public function __construct(User $user)
    {
        $this->user = $user;

        parent::__construct();
    }
}
