<?php

namespace App\Services\Braintree\Utils;

/**
 * Class Formatter.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class Formatter
{
    /**
     * @param $data
     *
     * @return mixed
     */
    public static function cast($data)
    {
        foreach ($data as $key => $value) {
            if ($value instanceof \Braintree\Base) {
                $data[$key] = self::cast($value->jsonSerialize());
            } else if ($value instanceof \DateTime) {
                $data[$key] = $value->format("c");
            } else if (\is_array($value)) {
                $data[$key] = self::cast($value);
            } else if (\is_string($value) && preg_match("/^-?(?:\d+|\d*\.\d+)$/", $value)) {
                $data[$key] = (float)$value;
            }
        }

        return $data;
    }
}
