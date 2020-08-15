<?php

declare(strict_types=1);

namespace Tests\Functional\Billing\Stripe\Models;

use Illuminate\Support\Str;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Training;
use Stripe\StripeClient;
use Tests\ApplicationTestCase;
use function factory;
use function sprintf;

/**
 * @covers \Railgun\Billing\Stripe\Models\StripeSellable
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

    public function testCreate(): void
    {
        $training = factory(Training::class)->create();

        $this->assertNotNull($training->stripe_product_id);
        $this->assertNotNull($training->stripe_price_id);
    }

    public function testCreateExistingProduct(): void
    {
        /** @var Training $training1 */
        $training1               = factory(Training::class)->create();
        $expectedStripeProductId = $training1->stripe_product_id;
        $expectedStripePriceId   = $training1->stripe_price_id;
        $training1->delete();

        /** @var Training $training2 */
        $training2 = factory(Training::class)->make(['name' => $training1->name]);
        $this->assertNull($training2->stripe_product_id);
        $this->assertNull($training2->stripe_price_id);

        $training2->save();

        $this->assertEquals($expectedStripeProductId, $training2->stripe_product_id);
        $this->assertEquals($expectedStripePriceId, $training2->stripe_price_id);
    }

    public function testCreateCorruptedPriceId(): void
    {
        $training = factory(Training::class)->create(['stripe_price_id' => 'corrupted']);

        $this->assertNotNull($training->stripe_price_id);
        $this->assertNotEquals('corrupted', $training->stripe_price_id);
    }

    public function testUpdateNotOnStripe(): void
    {
        /** @var Training $training */
        $training = factory(Training::class)->make();
        $training->saveWithoutEvents();

        $training->name = sprintf(
            '%s (%s)',
            Str::ucfirst($this->faker->words(3, true)),
            $this->faker->randomNumber(6),
        );
        $training->save();

        $this->assertStringStartsWith('prod_', $training->stripe_product_id);
        $this->assertStringStartsWith('price_', $training->stripe_price_id);
    }

    public function testUpdateProductName(): void
    {
        $training       = factory(Training::class)->create();
        $training->name = sprintf(
            '%s (%s)',
            Str::ucfirst($this->faker->words(3, true)),
            $this->faker->randomNumber(6),
        );
        $training->save();

        $this->assertEquals($training->name, $training->stripe_product->name);
        $this->assertEquals($training->name, $training->stripe_price->nickname);
    }

    public function testUpdatePrice(): void
    {
        $training        = factory(Training::class)->create();
        $training->price += 1000;
        $training->save();

        $this->assertEquals($training->price, $training->stripe_price->unit_amount);
    }
}
