<?php
namespace App\Services\Braintree;

/**
 * Class Formatter
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Services\Braintree
 * @since
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
            if ($value instanceof \DateTime) {
                $data[$key] = $value->format("c");
            } else if (is_array($value)) {
                $data[$key] = self::cast($value);
            } else if (is_string($value) && preg_match("/^-?(?:\d+|\d*\.\d+)$/", $value)) {
                $data[$key] = floatval($value);
            }
        }

        return $data;
    }
}
