<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Railgun\Billing\StripeSubscriptionBridge;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Subscription as StripeSubscription;

class SubscriptionsSync extends Command
{
    /** @var string $signature */
    protected $signature = 'subscriptions:sync';

    public function handle(): void
    {
        /** @var Collection $subscriptions */
        $subscriptions = Cache::rememberForever('stripe.subscriptions', function () {
            $this->line('Retrieving all subscriptions from Stripe');
            $subscriptions       = new Collection();
            $stripeSubscriptions = StripeSubscription::all(['status' => 'all', 'limit' => 100]);

            foreach ($stripeSubscriptions->autoPagingIterator() as $subscription) {
                $subscriptions->push($subscription);

                if ($subscriptions->count() % 100 === 0) {
                    $this->getOutput()->write('.');
                }
            }

            $this->output->newLine();

            return $subscriptions;
        });

        /** @var Collection|string[] $customerIds */
        $customerIds = User::query()
            ->whereNotNull('stripe_id')
            ->pluck('stripe_id');

        $this->line('Saving models');

        $subscriptions
            ->filter(fn(StripeSubscription $s) => $customerIds->search($s->customer))
            ->map(fn(StripeSubscription $s) => StripeSubscriptionBridge::toEloquent($s))
            ->tap(fn(Collection $self) => $this->getOutput()->progressStart($self->count()))
            ->each(function (Subscription $subscription) {
                $subscription->save();
                $this->getOutput()->progressAdvance();
            })
            ->tap(fn() => $this->getOutput()->progressFinish());
    }
}
