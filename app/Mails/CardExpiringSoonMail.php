<?php

declare(strict_types=1);

namespace App\Mails;

use App\Models\User;
use App\Services\Braintree\Models\Plan;
use Braintree\CreditCard;
use DateTime;
use stdClass;

class CardExpiringSoonMail extends BaseMail
{
    private $customer;
    private $card;

    /**
     * @param CreditCard|stdClass $card
     */
    public function __construct(User $customer, $card)
    {
        parent::__construct();
        $this->customer = $customer;
        $this->card     = $card;
    }

    public static function mock()
    {
        $card                 = new stdClass();
        $card->cardType       = 'Visa';
        $card->cardholderName = 'John Doe';
        $card->bin            = '123456';
        $card->maskedNumber   = '123456******1234';

        $subscription                          = new stdClass();
        $subscription->planId                  = 'v4_monthly';
        $subscription->nextBillingDate         = new DateTime();
        $subscription->nextBillingPeriodAmount = '17.90';

        $card->subscriptions = [$subscription];

        return new self(User::random(), $card);
    }

    /**
     * Build the mail.
     */
    public function build() : self
    {
        return $this->subject('Action requise: Mathrix - Votre abonnement Mode Machine')
            ->from('support@mathrix.fr')
            ->view('mails.subscriptions.card_expiring_soon', [
                'title' => 'Action requise: Mathrix - Votre abonnement Mode Machine',
                'customer' => $this->customer,
                'card' => $this->card,
                'subscription' => $this->card->subscriptions[0] ?? null,
                'plan' => Plan::find($this->card->subscriptions[0]->planId),
            ]);
    }
}
