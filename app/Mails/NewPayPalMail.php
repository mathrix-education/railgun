<?php

namespace App\Mails;

use App\Models\User;
use Braintree\Subscription;
use Braintree\SubscriptionSearch;
use Illuminate\Support\Facades\Cache;

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
    /** @var Subscription The PayPal subscription */
    private $subscription;


    /**
     * PasswordResetMail constructor.
     *
     * @param User $user the mail user
     * @param Subscription $subscription
     */
    public function __construct(User $user, Subscription $subscription)
    {
        $this->user = $user;
        $this->subscription = $subscription;

        parent::__construct();
    }


    /**
     * Mock the Mail.
     *
     * @return NewPayPalMail
     */
    public static function mock()
    {
        /** @var Subscription $subscription */
        $subscription = Cache::remember("dev.subscripion.paypal", 15, function () {
            return Subscription::search([
                SubscriptionSearch::inTrialPeriod()->is(false),
                SubscriptionSearch::planId()->is("v4_monthly"),
                SubscriptionSearch::status()->is(Subscription::ACTIVE)
            ])->firstItem();
        });

        /** @var User $user */
        $user = User::query()
            ->where("braintree_id", "=", $subscription->transactions[0]->customerDetails->id)
            ->firstOrFail();


        return new self($user, $subscription);
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
            ->view("mails.subscriptions.new-paypal", [
                "title" => "ACTION REQUISE - Abonnement au mode machine",
                "link" => env("APP_WEBROOT") . "/changement-paypal",
                "user" => $this->user,
                "subscription" => $this->subscription
            ]);
    }
}
