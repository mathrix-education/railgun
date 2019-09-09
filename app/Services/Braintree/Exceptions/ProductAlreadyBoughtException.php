<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Product;
use App\Models\User;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

class ProductAlreadyBoughtException extends Http400BadRequestException
{
    public function __construct(User $customer, Product $product, ?Throwable $previous = null)
    {
        $message = "User id `{$customer->id}` has already bought product id `{$product->id}`";

        parent::__construct([
            'user_id' => $customer->id,
            'product_id' => $customer->id,
        ], $message, $previous);
    }
}
