<?php

namespace App\Exceptions;

use App\Models\Discount;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Throwable;

/**
 * Class DiscountNotApplicableException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class DiscountNotApplicableException extends Http400BadRequestException
{
    protected const ERROR = "discount_not_applicable";


    /**
     * DiscountNotApplicableException constructor.
     *
     * @param Discount|string $discount
     * @param Throwable|null $previous
     */
    public function __construct($discount, Throwable $previous = null)
    {
        if ($discount instanceof Discount) {
            $discount = $discount->code;
        }

        parent::__construct(
            ["discount" => $discount],
            "Discount `MODEMACHINE2018` is not applicable on those products.",
            $previous
        );
    }
}
