<?php

namespace App\Services\Braintree\Utils;

use Braintree\Base as BraintreeBase;
use Carbon\Carbon;
use DateTime;

/**
 * Class Formatter.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class Formatter
{
    public const DATETIME_PATTERN = "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])(?:( [0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/";
    /**
     * @param $data
     *
     * @return mixed
     */
    public static function cast($data)
    {
        foreach ($data as $key => $value) {
            if ($value instanceof BraintreeBase) {
                $data[$key] = self::cast($value->jsonSerialize());
            } else if ($value instanceof DateTime) {
                $data[$key] = $value->format("c");
            } else if (is_array($value)) {
                $data[$key] = self::cast($value);
            } else if (is_string($value) && preg_match("/^-?(?:\d+|\d*\.\d+)$/", $value)) {
                $data[$key] = (float)$value;
            } else if (is_string($value) && preg_match(self::DATETIME_PATTERN, $value)) {
                $data[$key] = (new Carbon($value))->format("c");
            }
        }

        return $data;
    }
}
