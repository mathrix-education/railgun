<?php

declare(strict_types=1);

namespace Tests\Utils;

use Faker\Factory as FakerFactory;
use Railgun\Billing\Stripe\Repositories\PricesRepository;
use Railgun\Billing\Stripe\StripeClientRegistry;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use function app;
use function array_replace;
use function factory;

class StripeGenerator
{
    /**
     * @param mixed[] $options The generator options (user, client, price).
     *
     * @return mixed[] The generated objects.
     *
     * @throws ApiErrorException
     */
    public static function subscription(array $options = []): array
    {
        $faker = FakerFactory::create();

        if (!isset($options['user']) || !$options['user'] instanceof User) {
            $options['user'] = factory(User::class)->create();
        }

        $client = $options['client'] ?? StripeClientRegistry::CLIENT_MAIN;

        $plansPool = $client === StripeClientRegistry::CLIENT_MAIN
            ? PricesRepository::PREMIUM_PLANS
            : ['v5_monthly', 'v5_annually', 'v5_biannually'];

        $prices = app()->make(PricesRepository::class);

        $options = array_replace([
            'price'           => $client === StripeClientRegistry::CLIENT_MAIN
                ? $prices->get($faker->randomElement($plansPool))->id
                : $faker->randomElement($plansPool),
            'client'          => $client,
            'paymentMethodId' => 'pm_card_fr',
        ], $options);

        /** @var StripeClient $client */
        $client = app()->make(StripeClientRegistry::class)->make($options['client']);

        /** @var User $user */
        $user            = $options['user'];
        $customer        = $client->customers->create(['email' => $user->email]);
        $user->stripe_id = $customer->id;
        $user->save();

        $paymentMethod = $client->paymentMethods->attach($options['paymentMethodId'], ['customer' => $customer->id]);

        $stripeSubscription = $client->subscriptions->create([
            'customer'               => $customer->id,
            'items'                  => [['price' => $options['price']]],
            'default_payment_method' => $paymentMethod->id,
        ]);

        $subscription = Subscription::fromStripe($stripeSubscription);
        $subscription->save();

        $user->flushCache();

        return [
            'user'         => $user,
            'subscription' => $subscription,
            'stripe'       => [
                'customer'     => $customer,
                'subscription' => $stripeSubscription,
            ],
        ];
    }
}
