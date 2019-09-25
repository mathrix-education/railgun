<?php

declare(strict_types=1);

namespace App\Services\Stripe\Exceptions;

use App\Models\Product;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;
use function trans;

/**
 * Thrown when a customer tries to buy a product which he already owns.
 */
class ProductAlreadyBought extends Http400BadRequestException
{
    public function __construct(Product $product, ?Throwable $previous = null)
    {
        parent::__construct(
            ['product_id' => $product->id],
            trans('stripe.product.owned'),
            $previous
        );
    }
}
