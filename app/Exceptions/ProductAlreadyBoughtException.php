<?php

namespace App\Exceptions;

use App\Models\Product;
use App\Models\User;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Throwable;

/**
 * Class UserAlreadyOwnsProduct.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class ProductAlreadyBoughtException extends Http400BadRequestException
{
    protected const ERROR = "product_already_bought";

    public function __construct(User $customer, Product $product, Throwable $previous = null)
    {
        $message = "User id `{$customer->id}` has already bought product id `{$product->id}`";

        parent::__construct([
            "user_id" => $customer->id,
            "product_id" => $customer->id,
        ], $message, $previous);
    }
}
