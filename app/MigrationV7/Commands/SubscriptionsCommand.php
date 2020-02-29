<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Braintree\Gateway;
use Braintree\Subscription as BraintreeSubscription;
use Exception;
use Illuminate\Support\Collection;
use Railgun\Billing\Bridges\BraintreeSubscriptionBridge;
use Railgun\Billing\Bridges\StripeSubscriptionBridge;
use Railgun\Models\User;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use function app;

class SubscriptionsCommand extends V7Command
{
    /** @var string $signature */
    protected $signature = 'v7:subscriptions';

    private Gateway $gateway;

    public function handle(Gateway $gateway): void
    {
        $this->title('Importing subscriptions');
        $this->migrateTo('2020_02_25_182054_create_subscriptions_table');

        $previousBraintreeGateway = $gateway;
        $previousStripeKey        = Stripe::getApiKey();

        $this->gateway = app()->instance(Gateway::class, new Gateway([
            'environment' => 'production',
            'merchantId'  => 'rzyffphs7hk49gnj',
            'publicKey'   => '58c8zmmsg3w5jw5m',
            'privateKey'  => '12a4f58b0ae8a2240e676272d47227f3',
        ]));
        Stripe::setApiKey('sk_live_FclQbgqwPHYA5uFHQIJni5I6');

        $this->stripe();
        $this->braintree();

        Stripe::setApiKey($previousStripeKey);
        app()->instance(Gateway::class, $previousBraintreeGateway);
    }

    /**
     * Fetch the subscription from Stripe.
     */
    private function stripe(): void
    {
        /** @var Collection $subscriptions */
        $this->line('Retrieving subscriptions from Stripe');
        $subscriptions       = new Collection();
        $stripeSubscriptions = StripeSubscription::all(['status' => 'all', 'limit' => 100]);

        foreach ($stripeSubscriptions->autoPagingIterator() as $subscription) {
            $subscriptions->push($subscription);

            if ($subscriptions->count() % 100 !== 0) {
                continue;
            }

            $this->getOutput()->write('.');
        }

        $this->output->newLine();

        /** @var Collection|string[] $customerIds */
        $customerIds = User::query()
            ->whereNotNull('stripe_id')
            ->pluck('stripe_id');

        $this->line('Saving Stripe subscriptions');

        $subscriptions
            ->filter(fn(StripeSubscription $s) => $customerIds->search($s->customer))
            ->tap(fn(Collection $self) => $this->getOutput()->progressStart($self->count()))
            ->each(function (StripeSubscription $stripe) {
                $subscription = StripeSubscriptionBridge::toEloquent($stripe);
                $subscription->save();
                $this->getOutput()->progressAdvance();
            })
            ->tap(fn() => $this->getOutput()->progressFinish());
    }

    /**
     * Fetch the subscriptions from Braintree.
     */
    private function braintree(): void
    {
        $this->line('Retrieving subscriptions from Braintree');
        $subscriptions          = new Collection();
        $braintreeSubscriptions = $this->gateway->subscription()->search([]);

        $this->getOutput()->progressStart($braintreeSubscriptions->maximumCount());

        foreach ($braintreeSubscriptions as $subscription) {
            $subscriptions->push($subscription);
            $this->getOutput()->progressAdvance();
        }

        $this->getOutput()->progressFinish();

        /** @var Collection|string[] $customerIds */
        $customerIds = User::query()
            ->whereNotNull('braintree_id')
            ->pluck('braintree_id');

        $subscriptions
            ->tap(function (Collection $self) {
                $this->line('Saving Braintree subscriptions');
                $this->getOutput()->progressStart($self->count());
            })
            ->each(function (BraintreeSubscription $s) use ($customerIds) {
                $this->getOutput()->progressAdvance();

                try {
                    $subscription = BraintreeSubscriptionBridge::toEloquent($s);
                } catch (Exception $exception) {
                    return;
                }

                if (!$customerIds->search($subscription->customer_id)) {
                    return;
                }

                $subscription->save();
            });
    }
}
