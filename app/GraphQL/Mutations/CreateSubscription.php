<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Card;
use Railgun\Billing\StripeSubscriptionBridge;
use Railgun\Billing\SubscriptionUtils;
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

        $paymentMethodId = $args['paymentMethodId'];
        $planId          = $args['planId'];

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
                ['plan' => $planId],
            ],
            'metadata'               => [
                'user_id' => $user->id,
            ],
            'expand'                 => ['latest_invoice.payment_intent'],
        ];

        if (SubscriptionUtils::isPlanProrated($planId)) {
            // Add the billing cycle anchor if necessary
            $payload['billing_cycle_anchor'] = SubscriptionUtils::getProrationAnchor($planId)->getTimestamp();
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
