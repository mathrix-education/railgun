<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Bridges\StripeSubscriptionBridge;
use Railgun\Billing\Models\Plan;
use Railgun\GraphQL\Traits\SetupCustomer;
use Railgun\Models\Subscription;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;

class CreateSubscription
{
    use SetupCustomer;

    /**
     * @param null           $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return Subscription
     *
     * @throws ApiErrorException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $paymentMethodId = $args['paymentMethodId'];
        /** @noinspection PhpParamsInspection */
        $user = $this->setupCustomer($context->user(), $paymentMethodId);

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
        $subscription       = StripeSubscriptionBridge::toEloquent($stripeSubscription);
        $subscription->save();

        return $subscription;
    }
}
