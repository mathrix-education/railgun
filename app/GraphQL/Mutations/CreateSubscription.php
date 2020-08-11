<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Cache;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Exceptions\AlreadyPremiumException;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Jobs\UpsertSubscription;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\StripeClient;
use function array_keys;
use function collect;

/**
 * Mutation createSubscription.
 * Create a new subscription for the current user.
 */
class CreateSubscription
{
    protected Dispatcher   $dispatcher;
    protected StripeClient $client;

    public function __construct(Dispatcher $dispatcher, StripeClient $client)
    {
        $this->dispatcher = $dispatcher;
        $this->client     = $client;
    }

    protected function resolveLookupKey(string $lookupKey): string
    {
        $mappings = Cache::remember('prices.lookup_keys', 3600 * 24 * 7, function () {
            return collect($this->client->prices->all(['lookup_keys' => array_keys(ModeMachine::PRICES)])->data)
                ->mapWithKeys(static fn(Price $price) => [$price->lookup_key => $price->id]);
        });

        return $mappings[$lookupKey];
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

        if ($user->premium) {
            throw new AlreadyPremiumException($user->id);
        }

        $paymentMethodId = $args['paymentMethodId'];
        $customer        = $user->ensureStripeCustomer($paymentMethodId);

        $subscription = $this->client->subscriptions->create([
            'customer'               => $customer->id,
            'items'                  => [['price' => $this->resolveLookupKey($args['planId'])]],
            'default_payment_method' => $paymentMethodId,
            'expand'                 => ['latest_invoice.payment_intent'],
        ]);

        return $this->dispatcher->dispatchNow(new UpsertSubscription($subscription));
    }
}
