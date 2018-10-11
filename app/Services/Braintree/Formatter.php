<?php

namespace App\Services\Braintree;

/**
 * Class Formatter.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since
 *
 * @copyright Mathrix Education SA
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
            } elseif ($value instanceof \DateTime) {
                $data[$key] = $value->format("c");
            } elseif (\is_array($value)) {
                $data[$key] = self::cast($value);
            } elseif (\is_string($value) && preg_match("/^-?(?:\d+|\d*\.\d+)$/", $value)) {
                $data[$key] = (float) $value;
            }
        }

        return $data;
    }
}
