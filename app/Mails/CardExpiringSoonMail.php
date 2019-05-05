<?php

namespace App\Mails;

use App\Jobs\CardExpiringSoonJob;
use App\Models\User;
use App\Services\Braintree\Models\Plan;
use Braintree\CreditCard;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Class CardExpiringSoonMail.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.1.0
 */
class CardExpiringSoonMail extends BaseMail
{
    private $customer;
    private $card;


    public function __construct(User $customer, CreditCard $card)
    {
        parent::__construct();
        $this->customer = $customer;
        $this->card = $card;
    }


    public static function mock()
    {
        $currentMonth = Carbon::now()->month;
        $start = Carbon::create(2019, $currentMonth + 1, 1);
        $end = Carbon::create(2019, $currentMonth + 3, 1);

        $cards = Cache::remember(self::class . "_creditCards", 60, function () use ($start, $end) {
            return CardExpiringSoonJob::getCardsWithSubscriptionExpiringBetween($start, $end);
        });

        return new self(User::random(), $cards->random());
    }


    /**
     * Build the mail.
     *
     * @return self
     */
    public function build()
    {
        return $this->subject("Action requise: Mathrix - Votre abonnement Mode Machine")
            ->from("support@mathrix.fr")
            ->view("mails.subscriptions.card_expiring_soon", [
                "title" => "Action requise: Mathrix - Votre abonnement Mode Machine",
                "customer" => $this->customer,
                "card" => $this->card,
                "subscription" => $this->card->subscriptions[0] ?? null,
                "plan" => Plan::find($this->card->subscriptions[0]->planId)
            ]);
    }
}
