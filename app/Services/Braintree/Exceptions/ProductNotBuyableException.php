<?php

namespace App\Exceptions;

use App\Models\Product;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

/**
 * Class ProductNotBuyableException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class ProductNotBuyableException extends Http400BadRequestException
{
    public function __construct(Product $product, Throwable $previous = null)
    {
        parent::__construct(
            ["product_id" => $product->id],
            "Product `id` {$product->id} cannot be purchased",
            $previous
        );
    }
}
