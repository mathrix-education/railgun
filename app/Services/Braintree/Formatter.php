<?php
namespace App\Services\Braintree;

/**
 * Class Formatter.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Services\Braintree
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
