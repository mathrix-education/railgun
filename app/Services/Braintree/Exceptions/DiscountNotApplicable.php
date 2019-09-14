<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Discount;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

class DiscountNotApplicable extends Http400BadRequestException
{
    /**
     * @param Discount|string $discount
     */
    public function __construct($discount, ?Throwable $previous = null)
    {
        if ($discount instanceof Discount) {
            $discount = $discount->code;
        }

        parent::__construct(
            ['discount' => $discount],
            "Discount `{$discount}` is not applicable on those products.",
            $previous
        );
    }
}
