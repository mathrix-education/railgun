<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Stripe\Webhooks;

use Railgun\Billing\Cart\Cart;
use Railgun\Models\User;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Tests\Utils\CartInputGenerator;
use function factory;

class InvoicesWebhookTest extends WebhookTestCase
{
    /**
     * @throws ApiErrorException
     */
    public function testCartInvoice(): void
    {
        $cart = Cart::build(CartInputGenerator::make('trainings'));

        $invoice = Invoice::constructFrom([
            'object'   => Invoice::OBJECT_NAME,
            'id'       => $this->faker->regexify('in_[a-zA-Z0-9]{14}'),
            'customer' => $this->faker->regexify('cus_[a-zA-Z0-9]{14}'),
            'metadata' => $cart->toMetadata(),
        ]);

        $user = factory(User::class)->create([
            'stripe_id' => $invoice->customer,
        ]);

        $this->callStripeWebhook(Event::INVOICE_PAYMENT_SUCCEEDED, $invoice);

        $this->assertResponseOk();
        $this->assertEquals('Invoice handled', $this->getContent('message'));
        $this->seeInDatabase('acquisitions', ['transaction_id' => $invoice->id]);

        foreach ($cart->trainings as $training) {
            $this->assertTrue($user->owns($training));
        }
    }

    public function testIgnoredInvoice(): void
    {
        $invoice = Invoice::constructFrom([
            'object'   => Invoice::OBJECT_NAME,
            'id'       => $this->faker->regexify('in_[a-zA-Z0-9]{14}'),
            'customer' => $this->faker->regexify('cus_[a-zA-Z0-9]{14}'),
        ]);

        $this->callStripeWebhook(Event::INVOICE_PAYMENT_SUCCEEDED, $invoice);
        $this->assertResponseOk();

        $this->assertEquals('Invoice ignored', $this->getContent('message'));
    }
}
