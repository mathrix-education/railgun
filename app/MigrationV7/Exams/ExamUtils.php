<?php

declare(strict_types=1);

namespace App\MigrationV7\Exams;

use function count;
use function is_array;
use function is_int;
use function is_string;

class ExamUtils
{
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

    public static function hasNullValues(array $array): bool
    {
        foreach ($array as $val) {
            if ($val === null) {
                return true;
            }
        }

        return false;
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
}
