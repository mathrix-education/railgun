<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Bus\Dispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Stripe\Migrations\BraintreeMigration;
use Railgun\Billing\Stripe\Migrations\StripeLegacyMigration;
use Railgun\Billing\Stripe\Migrations\StripeMigration;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class UpdateSubscription
{
    protected Dispatcher   $dispatcher;
    protected StripeClient $client;

    public function __construct(Dispatcher $dispatcher, StripeClient $client)
    {
        $this->dispatcher = $dispatcher;
        $this->client     = $client;
    }

    /**
     * @param null           $_       The parent value, always null in this case.
     * @param mixed[]        $args    The mutation arguments (planId, paymentMethodId).
     * @param GraphQLContext $context The GraphQL context which holds the user.
     *
     * @return Subscription The updated subscription.
     *
     * @throws ApiErrorException
     */
    public function __invoke($_, array $args, GraphQLContext $context): Subscription
    {
        /** @var User $user */
        $user = $context->user();
        /** @var Subscription $subscription */
        $subscription    = Subscription::query()->findOrFail($args['id']);
        $paymentMethodId = $args['paymentMethodId'];

        if (!$subscription->user->is($user)) {
            // Throw a model not found exception to hide the fact that a subscription exists
            throw (new ModelNotFoundException())->setModel(Subscription::class, $args['id']);
        }

        if ($subscription->processor === Subscription::PROCESSOR_BRAINTREE_LEGACY) {
            $migration = (new BraintreeMigration($subscription, $user, $paymentMethodId));
        } elseif ($subscription->processor === Subscription::PROCESSOR_STRIPE_LEGACY) {
            $migration = (new StripeLegacyMigration($subscription, $user, $paymentMethodId));
        } else {
            $migration = (new StripeMigration($subscription, $user, $paymentMethodId));
        }

        return $migration->execute()->getSubscription();
    }
}
