<?php

declare(strict_types=1);

namespace Railgun\Buster\Utils;

use function count;
use function is_int;
use function is_string;

class Vector
{
    /**
     * @param mixed[] $vector The entry vector.
     *
     * @return bool If the vector contains only null values.
     */
    public static function isNull(array $vector): bool
    {
        if (count($vector) === 0) {
            return false;
        }

        foreach ($vector as $val) {
            if ($val !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed[] $vector The entry vector.
     *
     * @return bool If the vector contains only integer values.
     */
    public static function isInt(array $vector): bool
    {
        if (count($vector) === 0) {
            return false;
        }

        foreach ($vector as $val) {
            if (!is_int($val)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed[] $vector The entry vector.
     *
     * @return bool If the vector contains only string values.
     */
    public static function isString(array $vector): bool
    {
        if (count($vector) === 0) {
            return false;
        }

        foreach ($vector as $value) {
            if (!is_string($value)) {
                return false;
            }
        }

        return true;
    }
}
