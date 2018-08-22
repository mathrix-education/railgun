<?php
namespace App\Mails;

use App\Models\User;

/**
 * Class PasswordForgottenMail
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Mails
 * @since 1.0.1
 * @copyright Mathrix Education SA
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
     * Mock the Mail
     *
     * @return PasswordForgottenMail
     */
    public static function mock()
    {
        $user = User::random();
        return new PasswordForgottenMail($user);
    }


    /**
     * Build the Mail
     *
     * @return PasswordForgottenMail
     */
    public function build()
    {
        $this->subject("Modification de mot de passe");

        return $this->view("emails.users.passwordForgotten", [
            "title" => "Modification de mot de passe",
            "firstname" => $this->user->firstname ?? $this->user->username,
            "token" => $this->user->token
        ]);
    }
}
