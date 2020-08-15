<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Bus\Dispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Jobs\UpsertSubscription;
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
     * @return Subscription The newly created subscription.
     *
     * @throws ApiErrorException
     */
    public function __invoke($_, array $args, GraphQLContext $context): Subscription
    {
        /** @var User $user */
        $user = $context->user();
        /** @var Subscription $subscription */
        $subscription = Subscription::query()->findOrFail($args['id']);

        if ($user->stripe_id !== $subscription->customer_id) {
            // Throw a model not found exception to hide the fact that a subscription exists
            throw (new ModelNotFoundException())->setModel(Subscription::class, $args['id']);
        }

        $newPaymentMethod   = $user->attachStripePaymentMethod($args['paymentMethodId']);
        $stripeSubscription = $this->client->subscriptions->update($args['id'], [
            'default_payment_method' => $newPaymentMethod->id,
        ]);

        return $this->dispatcher->dispatchNow(new UpsertSubscription($stripeSubscription));
    }
}
