<?php

declare(strict_types=1);

namespace App\Services\Stripe\Webhooks;

use App\Models\Purchase;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\Invoice;
use Stripe\InvoiceLineItem;

/**
 * Handle invoice.payment_succeeded Stripe event.
 */
class InvoicePaymentSucceededWebhook extends BaseWebhook
{
    public const EVENT = 'invoice.payment_succeeded';

    /**
     * @param Invoice $invoice
     *
     * @return SuccessJsonResponse
     */
    public function handle($invoice): SuccessJsonResponse
    {
        // Handle products delivery
        /** @var InvoiceLineItem $lineItem */
        foreach ($invoice->lines as $lineItem) {
            if (!isset($lineItem->metadata['product_id'], $lineItem->metadata['user_id'])) {
                continue;
            }

            $purchase = new Purchase([
                'transaction_id' => $invoice->id,
                'user_id'        => $lineItem->metadata['user_id'],
                'product_id'     => $lineItem->metadata['product_id'],
            ]);
            $purchase->save();
        }

        return new SuccessJsonResponse([
            'event_type' => self::EVENT,
            'invoice'    => [
                'id' => $invoice->id,
            ],
        ]);
    }
}
