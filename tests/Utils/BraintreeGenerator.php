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
use Throwable;
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

        do {
            $sleep = isset($sleep) ? $sleep + 1 : 0;

            try {
                /** @var PaymentMethod $btPaymentMethod */
                $btPaymentMethod = $gateway->paymentMethod()->create([
                    'customerId'         => $btCustomer->id,
                    'paymentMethodNonce' => $faker->randomElement([
                        Nonces::$transactable,
                        Nonces::$transactableVisa,
                        Nonces::$transactableMasterCard,
                        Nonces::$transactableAmEx,
                        Nonces::$transactableDebit,
                    ]),
                ])->paymentMethod;

                /** @var BraintreeSubscription $btSubscription */
                $btSubscription = $gateway->subscription()->create([
                    'planId'             => $options['planId'],
                    'paymentMethodToken' => $btPaymentMethod->token,
                ])->subscription;
                break;
            } catch (Throwable $exception) {
                continue;
            }
        } while (sleep($sleep) === 0 && $sleep <= 15);

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
