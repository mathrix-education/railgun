<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Braintree\Gateway;
use Braintree\Subscription as BraintreeSubscription;
use Exception;
use Illuminate\Support\Collection;
use Railgun\Billing\BraintreeSubscriptionBridge;
use Railgun\Billing\StripeSubscriptionBridge;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Subscription as StripeSubscription;

class SubscriptionsCommand extends V7Command
{
    /** @var string $signature */
    protected $signature = 'v7:subscriptions';

    private Gateway $gateway;

    public function handle(Gateway $gateway): void
    {
        $this->gateway = $gateway;

        $this->title('Importing subscriptions');
        $this->migrateTo('2020_02_25_182054_create_subscriptions_table');

        $this->stripe();
        $this->braintree();
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

            if ($subscriptions->count() % 100 === 0) {
                $this->getOutput()->write('.');
            }
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
