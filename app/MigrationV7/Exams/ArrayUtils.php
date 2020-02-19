<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Exams;

use function count;
use function is_array;
use function is_int;
use function is_string;

class ArrayUtils
{
    /**
     * Check if the given array is only composed of null values.
     *
     * @param array $array The entry array.
     *
     * @return bool
     */
    public static function isNullArray(array $array): bool
    {
        if (count($array) === 0) {
            return false;
        }

        foreach ($array as $val) {
            if ($val !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the given array have at least one null value.
     *
     * @param array $array The entry array.
     *
     * @return int|bool The first null value index, false otherwise.
     */
    public static function hasNullValues(array $array)
    {
        foreach ($array as $index => $val) {
            if ($val === null) {
                return $index;
            }
        }

        return false;
    }

    /**
     * @param array $matrix
     *
     * @return bool
     */
    public static function isIntegerMatrix(array $matrix): bool
    {
        foreach ($matrix as $line) {
            if (!is_array($line)) {
                continue;
            }

            $res = self::isIntegerArray($line);

            if (!$res) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check that an array is only composed of integers.
     *
     * @param array $array
     *
     * @return bool
     */
    public static function isIntegerArray(array $array): bool
    {
        if (count($array) === 0) {
            return false;
        }

        foreach ($array as $val) {
            if (!is_int($val)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $matrix
     *
     * @return bool
     */
    public static function isStringMatrix(array $matrix): bool
    {
        foreach ($matrix as $line) {
            if (!is_array($line)) {
                continue;
            }

            $res = self::isStringArray($line);

            if (!$res) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check that an array is only composed of strings.
     *
     * @param array $line
     *
     * @return bool
     */
    public static function isStringArray(array $line): bool
    {
        if (count($line) === 0) {
            return false;
        }

        foreach ($line as $value) {
            if (!is_string($value)) {
                return false;
            }
        }

        return true;
    }
}
