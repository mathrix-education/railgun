<?php

declare(strict_types=1);

namespace App\Services\Stripe\Utils;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class InvoiceUtils
{
    /**
     * Get the InvoiceItem payload for a given product.
     *
     * @param Product $product
     *
     * @return array
     */
    public static function toInvoiceItemPayload(Product $product): array
    {
        $user = Auth::user();

        return [
            'currency'    => 'eur',
            'customer'    => $user->stripe_id,
            'description' => $product->name,
            'metadata'    => [
                'user_id'    => $user->id,
                'product_id' => $product->id,
            ],
            'quantity'    => 1,
            'unit_amount' => $product->price,
        ];
    }
}
