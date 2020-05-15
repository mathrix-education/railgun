<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Exceptions\OwnershipException;
use Railgun\Jobs\UpsertSubscription;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;
use function dispatch_now;

class UpdateSubscription
{
    /**
     * @param null           $rootValue The parent value, always null in this case.
     * @param mixed[]        $args      The mutation arguments (planId, paymentMethodId).
     * @param GraphQLContext $context   The GraphQL context which holds the user.
     *
     * @return Subscription The newly created subscription.
     *
     * @throws ApiErrorException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context): Subscription
    {
        /** @var User $user */
        $user = $context->user();
        /** @var Subscription $subscription */
        $subscription = Subscription::query()->findOrFail($args['id']);

        if ($user->stripe_id !== $subscription->customer_id) {
            throw new OwnershipException();
        }

        $newPaymentMethod = $user->attachPaymentMethod($args['paymentMethodId']);

        $stripeSubscription = StripeSubscription::update($args['id'], [
            'default_payment_method' => $newPaymentMethod->id,
        ]);

        return dispatch_now(new UpsertSubscription($stripeSubscription));
    }
}
