<?php

namespace App\Services\Stripe\Pipelines;

use App\Services\Stripe\SubscriptionUtils;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;
use Stripe\Invoice;
use Stripe\Subscription;

/**
 * Class SubscriptionCreationPipeline.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class SubscriptionCreationPipeline extends Pipeline
{
    /** @var string The subscription plan */
    public $plan;
    /** @var Subscription $subscription */
    public $subscription;
    /** @var Invoice $invoice */
    public $invoice;


    /**
     * @throws ValidationException
     */
    protected function initialize(): void
    {
        $this->validate($this->request, [
            "plan" => "required|in:" . implode(",", SubscriptionUtils::PLANS),
        ]);

        $this->plan = $this->request->json("plan");

        if (!$this->user->isStripeCustomer()) {
            (new CustomerCreationPipeline())->flow();
        }
    }


    protected function exchange(): void
    {
        $payload = [
            "customer" => $this->user->stripe_id,
            "default_source" => $this->request->json("card.id")
                ?? $this->user->asStripeCustomer()->default_source,
            "items" => [
                ["plan" => $this->plan]
            ],
            "metadata" => [
                "user_id" => $this->user->id,
            ],
            "expand" => ["latest_invoice.payment_intent"]
        ];

        if (SubscriptionUtils::isPlanProrated($this->plan)) {
            $payload = array_replace_recursive($payload, [
                "billing_cycle_anchor" => SubscriptionUtils::getProrationAnchor($this->plan)->getTimestamp()
            ]);
        }

        $this->subscription = Subscription::create($payload);
    }


    protected function finalize(): void
    {
        $this->user->invalidate();
    }
}
