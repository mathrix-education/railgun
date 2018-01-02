<?php
namespace App\Mails;

/**
 * Class WelcomeMail
 *
 * @author Mathrix Back-End Team
 * @package App\Mails
 * @since 1.0.1
 * @copyright Mathrix SA
 */
class WelcomeMail extends UserMail
{
    public function build()
    {
        $this->subject = "Bienvenue sur Mathrix.fr";

        return $this->view("emails.users.welcome", [
            "title" => "Bienvenue sur Mathrix.fr",
            "firstname" => $this->user->firstname ?? $this->user->username
        ]);
    }
}
