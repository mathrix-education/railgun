<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Product;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

class ProductNotBuyableException extends Http400BadRequestException
{
    public function __construct(Product $product, ?Throwable $previous = null)
    {
        parent::__construct(
            ['product_id' => $product->id],
            "Product `id` {$product->id} cannot be purchased",
            $previous
        );
    }
}
