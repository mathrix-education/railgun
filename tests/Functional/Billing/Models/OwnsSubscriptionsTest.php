<?php

declare(strict_types=1);

namespace Tests\Functional\Billing\Models;

use Illuminate\Support\Carbon;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Tests\Functional\Models\ModelTestCase;
use function factory;

/**
 * @coversDefaultClass \Railgun\Billing\Models\OwnsSubscriptions
 */
class OwnsSubscriptionsTest extends ModelTestCase
{
    /**
     * @covers ::getPremiumAttribute
     */
    public function testPremiumByPermissions(): void
    {
        $user = factory(User::class)->create(['permissions' => ['foo']]);

        $this->assertTrue($user->premium);
    }

    /**
     * @covers ::getPremiumAttribute
     */
    public function testPremiumByPremiumBypass(): void
    {
        $user = factory(User::class)->create(['premium_bypass' => true]);

        $this->assertTrue($user->premium);
    }

    /**
     * @covers ::getPremiumAttribute
     */
    public function testPremiumByPremiumBypassExpiresAt(): void
    {
        $user = factory(User::class)->create(['premium_bypass_expires_at' => Carbon::now()->addDay()]);

        $this->assertTrue($user->premium);
    }

    /**
     * @covers ::getPremiumAttribute
     */
    public function testPremiumBySubscriptions(): void
    {
        $subscription = factory(Subscription::class)->create();

        $user = factory(User::class)->create([
            'stripe_id'    => $subscription->customer_id,
            'braintree_id' => $this->faker->regexify('cus_[0-9a-f]{32}'),
        ]);

        $this->assertTrue($user->premium);
    }
}
