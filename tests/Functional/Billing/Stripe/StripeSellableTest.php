<?php

declare(strict_types=1);

namespace Tests\Functional\Billing\Stripe;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Training;
use Stripe\StripeClient;
use Tests\ApplicationTestCase;
use function factory;

/**
 * @coversDefaultClass \Railgun\Billing\Stripe\Models\StripeSellable
 */
class StripeSellableTest extends ApplicationTestCase
{
    use DatabaseTransactions;

    protected StripeClient $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->app->make(StripeClient::class);
    }

    protected function makeTraining(): Training
    {
        return factory(Training::class)->make();
    }

    protected function createTraining(): Training
    {
        return Training::withoutEvents(static fn() => factory(Training::class)->create());
    }

    /**
     * @covers ::getIsSellableAttribute
     */
    public function testIsSellable(): void
    {
        $this->assertTrue($this->makeTraining()->is_sellable);
    }

    /**
     * @covers ::getStripeProductIdAttribute
     */
    public function testGetStripeProductIdAttribute(): void
    {
        $this->assertMatchesRegularExpression('/training_\d+/', $this->createTraining()->stripe_product_id);
    }

    public function testCreateStripeProductNotExisting(): void
    {
        $training = factory(Training::class)->create();

        $this->assertNotNull($training->stripe_price_id);
    }

    public function testCreateStripeProductExisting(): void
    {
        /** @var Training $training1 */
        $training1             = factory(Training::class)->create();
        $expectedStripePriceId = $training1->stripe_price_id;
        $training1->delete();

        /** @var Training $training2 */
        $training2 = factory(Training::class)->make(['id' => $training1->id]);
        $this->assertNull($training2->stripe_price_id);

        $training2->save();

        $this->assertEquals($expectedStripePriceId, $training2->stripe_price_id);
    }

    public function testCreateStripeProductCorruptedPriceId(): void
    {
        $training = factory(Training::class)->create(['stripe_price_id' => 'corrupted']);

        $this->assertNotNull($training->stripe_price_id);
        $this->assertNotEquals('corrupted', $training->stripe_price_id);
    }
}
