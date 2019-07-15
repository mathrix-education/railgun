<?php

namespace App\Exceptions;

use App\Models\Discount;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

/**
 * Class DiscountExpiredException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class DiscountExpiredException extends Http400BadRequestException
{
    /**
     * DiscountExpiredException constructor.
     *
     * @param Discount|string $discount
     * @param Throwable|null $previous
     */
    public function __construct($discount, Throwable $previous = null)
    {
        if ($discount instanceof Discount) {
            $discount = $discount->code;
        }

        parent::__construct(["discount" => $discount], "Discount `$discount` has expired.", $previous);
    }
}
