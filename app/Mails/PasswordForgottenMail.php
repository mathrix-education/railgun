<?php
namespace App\Mails;

/**
 * Class PasswordForgottenMail
 *
 * @author Mathrix Back-End Team
 * @package App\Mails
 * @since 1.0.1
 * @copyright Mathrix SA
 * @cover
 */
class PasswordForgottenMail extends UserMail
{
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
