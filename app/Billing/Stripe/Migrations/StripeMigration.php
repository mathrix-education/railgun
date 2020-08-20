<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Migrations;

use Railgun\Jobs\UpsertSubscription;

class StripeMigration extends Migration
{
    public function run(): Migration
    {
        $this->stripeSubscription = $this->client->subscriptions->update($this->previous->id, [
            'default_payment_method' => $this->paymentMethodId,
        ]);

        return $this;
    }

    public function finalize(): Migration
    {
        $this->subscription = $this->dispatcher->dispatchNow(new UpsertSubscription($this->stripeSubscription));

        return $this;
    }
}
