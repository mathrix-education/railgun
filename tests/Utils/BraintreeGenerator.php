<?php

declare(strict_types=1);

namespace Tests\Utils;

use Braintree\Customer as BraintreeCustomer;
use Braintree\Gateway;
use Braintree\PaymentMethod;
use Braintree\Subscription as BraintreeSubscription;
use Braintree\Test\Nonces;
use Faker\Factory as FakerFactory;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use RuntimeException;
use function app;
use function array_replace;
use function factory;
use function sleep;

class BraintreeGenerator
{
    /**
     * @param mixed[] $options The generator options (user, planId).
     *
     * @return mixed[] The generated objects.
     */
    public static function subscription(array $options = []): array
    {
        $gateway = app()->make(Gateway::class);
        $faker   = FakerFactory::create();

        if (!isset($options['user']) || !$options['user'] instanceof User) {
            $options['user'] = factory(User::class)->create();
        }

        $options = array_replace([
            'planId' => $faker->randomElement(['v4_monthly', 'v4_annually', 'v4_biannually']),
        ], $options);

        /** @var User $user */
        $user = $options['user'];
        /** @var BraintreeCustomer $btCustomer */
        $btCustomer         = $gateway->customer()->create(['email' => $user->email])->customer;
        $user->braintree_id = $btCustomer->id;
        $user->save();

        $btSubscription = null;
        $sleep          = 1;

        do {
            $sleep *= 2;

            $result = $gateway->paymentMethod()->create([
                'customerId'         => $btCustomer->id,
                'paymentMethodNonce' => $faker->randomElement([
                    Nonces::$transactable,
                    Nonces::$transactableVisa,
                    Nonces::$transactableMasterCard,
                    Nonces::$transactableAmEx,
                    Nonces::$transactableDebit,
                ]),
            ]);

            if (!$result->success) {
                continue;
            }

            /** @var PaymentMethod $btPaymentMethod */
            $btPaymentMethod = $result->paymentMethod;

            $result = $gateway->subscription()->create([
                'planId'             => $options['planId'],
                'paymentMethodToken' => $btPaymentMethod->token,
            ]);

            if (!$result->success) {
                continue;
            }

            /** @var BraintreeSubscription $btSubscription */
            $btSubscription = $result->subscription;
            break;
        } while (sleep($sleep) === 0 && $sleep <= 32);

        if (!$btSubscription) {
            throw new RuntimeException('Unable to generate Braintree subscription');
        }

        $subscription = Subscription::fromBraintree($btSubscription);
        $subscription->save();

        $user->flushCache();

        return [
            'user'         => $user,
            'subscription' => $subscription,
            'braintree'    => [
                'customer'     => $btCustomer,
                'subscription' => $btSubscription,
            ],
        ];
    }
}
