<?php
namespace App\Mails;

/**
 * Class PasswordResetMail
 *
 * @author Mathrix Back-End Team
 * @package App\Mails
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class PasswordResetMail extends UserMail
{
    public function build()
    {
        $this->subject("Mot de passe réinitialisé");

        return $this->view("emails.users.passwordReset", [
            "title" => "Mot de passe réinitialisé",
            "firstname" => $this->user->firstname ?? $this->user->username
        ]);
    }
}
