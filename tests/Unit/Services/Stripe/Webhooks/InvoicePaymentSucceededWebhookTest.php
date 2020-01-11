<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stripe\Webhooks;

use App\Services\Stripe\Webhooks\InvoicePaymentSucceededWebhook;
use stdClass;
use Stripe\Invoice;
use Tests\APIStatelessTestCase;

/**
 * Test InvoicePaymentSucceededWebhook class.
 */
class InvoicePaymentSucceededWebhookTest extends APIStatelessTestCase
{
    private function metadata($userId, $productId): stdClass
    {
        $lineItem           = new stdClass();
        $lineItem->metadata = [
            'user_id'    => $userId,
            'product_id' => $productId,
        ];

        return $lineItem;
    }

    /**
     * @covers \App\Services\Stripe\Webhooks\InvoicePaymentSucceededWebhook::handle
     */
    public function testHandle(): void
    {
        /** @var Invoice $mock */
        $mock        = new stdClass();
        $mock->id    = 'in_123';
        $mock->lines = [
            $this->metadata(1, 1),
            $this->metadata(1, 2),
        ];

        $webhook = new InvoicePaymentSucceededWebhook();
        $webhook->handle($mock);

        $this->assertInDatabase('purchases', ['transaction_id' => 'in_123', 'user_id' => 1, 'product_id' => 1]);
        $this->assertInDatabase('purchases', ['transaction_id' => 'in_123', 'user_id' => 1, 'product_id' => 2]);
    }
}
