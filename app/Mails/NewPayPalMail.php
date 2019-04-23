<?php

namespace App\Mails;

use App\Models\User;

/**
 * Class NewPayPalMail.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-dev
 */
class NewPayPalMail extends BaseMail
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
     * @return NewPayPalMail
     */
    public static function mock()
    {
        /** @var User $user */
        $user = User::random();

        return new self($user);
    }


    /**
     * Build the mail.
     *
     * @return NewPayPalMail
     */
    public function build()
    {
        return $this->subject("ACTION REQUISE - Abonnement au mode machine")
            ->from("support@mathrix.fr")
            ->priority(1)
            ->view("mails.subscriptions.new-paypal", [
                "title" => "ACTION REQUISE - Abonnement au mode machine",
                "link" => env("APP_WEBROOT") . "/paiement/mise-a-jour",
                "user" => $this->user
            ]);
    }
}
