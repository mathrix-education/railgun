<?php


namespace App\Mails;


use App\Models\User;

class SubscriptionRenewalSoonMail extends BaseMail
{
    /** @var User The mail user */
    private $user;


    /**
     * SubscriptionRenewalSoonMail constructor.
     *
     * @param User $user the mail user
     */
    public function __construct(User $user)
    {
        $this->user = $user;

        parent::__construct();
    }


    public static function mock()
    {
        return new self(User::random());
    }


    /**
     * Build the Mail.
     *
     * @return static
     */
    public function build()
    {
        $this->subject("[IMPORTANT] Renouvellement de votre abonnement Mode Machine");

        return $this
            ->from("support@mathrix.fr")
            ->view("mails.subscription_renewal_soon", [
                "display_name" => $this->user->firstname ?? $this->user->username,
            ]);
    }
}
