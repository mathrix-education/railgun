<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Bridges\StripeSubscriptionBridge;
use Railgun\Billing\Exceptions\Card;
use Railgun\Billing\Models\Plan;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\Subscription as StripeSubscription;

class CreateSubscription
{
    /**
     * @param null           $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return Subscription
     *
     * @throws ApiErrorException
     *
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        /** @var User $user */
        $user = Auth::user();
        $plan = new Plan($args['planId']);

        $paymentMethodId = $args['paymentMethodId'];

        if ($user->isStripeCustomer()) {
            $user->attachPaymentMethod($paymentMethodId);
        } else {
            $customer = $user->createCustomer($paymentMethodId);

            $paymentMethodId = $customer->invoice_settings->default_payment_method;
        }

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

        try {
            $stripeSubscription = StripeSubscription::create($payload);
        } catch (CardException $cardException) {
            throw new Card($cardException);
        }

        $subscription = StripeSubscriptionBridge::toEloquent($stripeSubscription);
        $subscription->save();

        return $subscription;
    }
}
