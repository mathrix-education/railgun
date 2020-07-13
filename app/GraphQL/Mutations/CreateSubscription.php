<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Exceptions\AlreadyPremiumException;
use Railgun\Billing\Models\Plan;
use Railgun\Jobs\UpsertSubscription;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;
use function dispatch_now;

/**
 * Mutation createSubscription.
 * Create a new subscription for the current user.
 */
class CreateSubscription
{
    /**
     * @param null           $_       The parent value, always null in this case.
     * @param mixed[]        $args    The mutation arguments (planId, paymentMethodId).
     * @param GraphQLContext $context The GraphQL context which holds the user.
     *
     * @return Subscription The newly created subscription.
     *
     * @throws ApiErrorException
     */
    public function __invoke($_, array $args, GraphQLContext $context): Subscription
    {
        /** @var User $user */
        $user = $context->user();

        if ($user->premium) {
            // Check if the user is already premium
            throw new AlreadyPremiumException($user->id);
        }

        $paymentMethodId = $args['paymentMethodId'];
        $user->createCustomer($paymentMethodId);

        $plan = new Plan($args['planId']);

        $payload = [
            'customer'               => $user->stripe_id,
            'default_payment_method' => $paymentMethodId,
            'items'                  => [
                ['plan' => $plan->id],
            ],
            'metadata'               => [
                'user_id' => $user->id,
            ],
            'expand'                 => ['latest_invoice.payment_intent'],
        ];

        if ($plan->prorated) {
            // Add the billing cycle anchor if necessary
            $payload['billing_cycle_anchor'] = $plan->proration_anchor->getTimestamp();
        }

        $stripeSubscription = StripeSubscription::create($payload);

        return dispatch_now(new UpsertSubscription($stripeSubscription));
    }
}
