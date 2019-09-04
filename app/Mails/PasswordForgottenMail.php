<?php

namespace App\Mails;

use App\Models\User;

/**
 * Class PasswordForgottenMail.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.1
 */
class PasswordForgottenMail extends BaseMail
{
    /** @var User The mail user */
    protected $user;


    /**
     * PasswordForgottenMail constructor.
     *
     * @param User $user the mail user
     */
    public function __construct(User $user)
    {
        $this->user = $user;

        parent::__construct();
    }


    /**
     * Mock the Mail.
     *
     * @return PasswordForgottenMail
     */
    public static function mock()
    {
        $user = User::random();

        return new self($user);
    }


    /**
     * Build the Mail.
     *
     * @return PasswordForgottenMail
     */
    public function build()
    {
        $this->subject("Modification de mot de passe");

        return $this->view("mails.password_forgotten", [
            "title" => "Modification de mot de passe",
            "firstname" => $this->user->firstname ?? $this->user->username,
            "token" => $this->user->token,
        ]);
    }
}
