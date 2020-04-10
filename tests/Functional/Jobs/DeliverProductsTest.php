<?php

declare(strict_types=1);

namespace Tests\Functional\Jobs;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Railgun\Billing\Cart\Cart;
use Railgun\Jobs\DeliverProducts;
use Railgun\Models\User;
use RuntimeException;
use Stripe\Invoice;
use Tests\ApplicationTestCase;
use function dispatch_now;
use function factory;

/**
 * @testdox DeliverProducts Job
 * @coversDefaultClass \Railgun\Jobs\DeliverProducts
 */
class DeliverProductsTest extends ApplicationTestCase
{
    /**
     * @testdox ignores the invoice if the metadata type is {@see Cart::IDENTIFIER}.
     * @covers ::__construct
     * @covers ::handle
     */
    public function testUnsupportedType(): void
    {
        $user = factory(User::class)->create(['stripe_id' => 'cus_' . $this->faker->password(8, 8)]);

        $invoice = Invoice::constructFrom([
            'customer' => $user->stripe_id,
            'metadata' => [
                'type' => 'idontexist',
            ],
        ]);

        $this->assertNull(dispatch_now(new DeliverProducts($invoice)));
    }

    /**
     * @testdox throws an exception if the invoice is not paid.
     * @covers ::__construct
     * @covers ::handle
     */
    public function testInvoiceNotPaid(): void
    {
        $user = factory(User::class)->create(['stripe_id' => 'cus_' . $this->faker->password(8, 8)]);

        $invoice = Invoice::constructFrom([
            'customer' => $user->stripe_id,
            'paid'     => false,
            'metadata' => [
                'type' => Cart::IDENTIFIER,
            ],
        ]);

        $this->expectException(RuntimeException::class);
        dispatch_now(new DeliverProducts($invoice));
    }

    /**
     * @testdox Do not report any exception on sandbox environment.
     * @covers ::__construct
     * @covers ::handle
     */
    public function testDeliverSandboxNonExistingUser(): void
    {
        $this->app['config']->set('services.stripe.sandbox', true);

        $expected = DB::table('acquisitions')->count();
        $invoice  = Invoice::constructFrom(['customer' => 'idontexist']);

        $this->assertNull(dispatch_now(new DeliverProducts($invoice)));
        $this->assertEquals($expected, DB::table('acquisitions')->count());
    }

    /**
     * @testdox Reports an exception on non-existing user in production environment.
     * @covers ::__construct
     * @covers ::handle
     */
    public function testDeliverProductionNonExistingUser(): void
    {
        $this->app['config']->set('services.stripe.sandbox', false);

        $invoice = Invoice::constructFrom([
            'customer' => 'idontexist',
        ]);

        $this->expectException(ModelNotFoundException::class);
        dispatch_now(new DeliverProducts($invoice));
    }
}
