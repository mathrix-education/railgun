<?php
namespace App\Mails;

/**
 * Class PasswordForgottenMail
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Mails
 * @since 1.0.1
 * @copyright Mathrix Education SA
 * @codeCoverageIgnore
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
