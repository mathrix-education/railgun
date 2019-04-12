<?php

namespace App\Mails;

use App\Models\User;

/**
 * Class PasswordResetMail.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.1
 */
class PasswordResetMail extends BaseMail
{
    /** @var User The mail user */
    private $user;


    /**
     * PasswordResetMail constructor.
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
     * @return PasswordResetMail
     */
    public static function mock()
    {
        $user = User::random();

        return new self($user);
    }


    /**
     * Build the Mail.
     *
     * @return PasswordResetMail
     */
    public function build()
    {
        $this->subject("Mot de passe réinitialisé");

        return $this->view("mails.users.passwordReset", [
            "title" => "Mot de passe réinitialisé",
            "firstname" => $this->user->firstname ?? $this->user->username,
        ]);
    }
}
