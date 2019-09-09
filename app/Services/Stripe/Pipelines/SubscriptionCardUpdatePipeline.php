<?php

declare(strict_types=1);

namespace App\Services\Stripe\Pipelines;

use App\Services\Stripe\Models\StripeCustomer;
use Stripe\Card as StripeCard;
use Stripe\Subscription as StripeSubscription;

class SubscriptionCardUpdatePipeline extends Pipeline
{
    /** @var StripeSubscription The subscription. */
    public $subscription;
    /** @var string The subscription id. */
    private $subscriptionId;

    public function __construct(string $subscriptionId)
    {
        parent::__construct();

        $this->subscriptionId = $subscriptionId;
    }

    protected function initialize() : void
    {
        $this->validate($this->request, [
            'card.id' => 'required_without:card.token',
            'card.token' => 'required_without:card.id',
        ]);

        $this->subscription = StripeSubscription::retrieve([
            'id' => $this->subscriptionId,
            'expand' => ['default_source'],
        ]);
    }

    protected function exchange() : void
    {
        if ($this->request->has('card.token')) {
            /** @var StripeCard $card */
            $card = (new CustomerCardAttachPipeline())->flow()->card;
            /** @var StripeCard $subscriptionCard */
            $subscriptionCard = $this->subscription->default_source;

            if ($card->fingerprint === $subscriptionCard->fingerprint) {
                // The requested card is the same as the one attached to the subscription, aborting here.
                return;
            }

            $cardId = $card->id;
        }

        $this->subscription = StripeSubscription::update($this->subscriptionId, [
            'default_source' => $cardId ?? $this->request->json('card.id'),
            'expand' => ['default_source', 'latest_invoice.payment_intent'],
        ]);
    }

    protected function finalize() : void
    {
        $this->user->invalidate(StripeCustomer::$StripeSubscriptionsCacheKey);
    }
}
