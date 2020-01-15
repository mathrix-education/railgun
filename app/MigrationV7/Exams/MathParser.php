<?php

declare(strict_types=1);

namespace App\MigrationV7\Exams;

use function array_filter;
use function array_map;
use function implode;
use function strlen;
use function strpos;
use function substr;
use function trim;

class MathParser
{
    /**
     * Extract math (inline and block equations) from a string.
     *
     * @param string $str The entry string.
     *
     * @return array
     */
    public static function extract(string $str): array
    {
        $parsed = [];
        $length = strlen($str);

        if (strpos($str, '$$') === 0) {
            $context = '$$';
        } elseif (strpos($str, '$') === 0) {
            $context = '$';
        } else {
            $context = null;
        }
        $start = 0;

        for ($i = $context === null ? 0 : strlen($context); $i < $length; $i++) {
            if ($str[$i] !== '$') {
                continue;
            }

            if ($context === null) {
                // Opening inline equation
                $context  = '$';
                $parsed[] = substr($str, $start, $i - $start);
                $start    = $i;
            } elseif ($context === '$') {
                if ($str[$i - 1] !== '$') {
                    // Closing inline equation
                    $context  = null;
                    $parsed[] = substr($str, $start, $i - $start + 1);
                    $start    = $i + 1;
                } else {
                    // Opening block equation
                    $context = '$$';
                    $start   = $i - 1;
                }
            } elseif ($context === '$$') {
                // Closing block equation
                $context  = null;
                $parsed[] = substr($str, $start, $i - $start + 2);
                $start    = $i + 2;
                $i++;
            }
        }

        $parsed = array_map(static function ($str) {
            return trim($str);
        }, $parsed);
        $parsed = array_filter($parsed, static function ($str) {
            return !empty($str);
        });

        return $parsed;
    }

    /**
     * Check if the given string is a inline equation (i.e. starts and ends with a single dollar).
     *
     * @param string $str The entry string.
     *
     * @return bool
     */
    public static function isInlineMath(string $str): bool
    {
        $startsWithSingleDollar = strpos($str, '$') === 0 && $str[1] !== '$';
        $endsWithSingleDollar   = $str[-1] === '$' && $str[-2] !== '$';

        return $startsWithSingleDollar && $endsWithSingleDollar;
    }

    /**
     * Check if the given string is a block equation (i.e. starts and ends with a double dollar).
     *
     * @param string $str The entry string.
     *
     * @return bool
     */
    public static function isBlockMath(string $str): bool
    {
        $startsWithDoubleDollar = strpos($str, '$$') === 0;
        $endsWithDoubleDollar   = substr($str, -2) === '$$';

        return $startsWithDoubleDollar && $endsWithDoubleDollar;
    }

    /**
     * Check if the given string is an equation (single or block).
     *
     * @param string $str The entry string.
     *
     * @return bool
     */
    public static function isMath(string $str): bool
    {
        return self::isInlineMath($str) || self::isBlockMath($str);
    }

    public static function toWall(string $str)
    {
        $parsed = self::extract($str);
        $buffer = [];
        $wall   = [];

        foreach ($parsed as $item) {
            if (self::isBlockMath($item)) {
                if (!empty($buffer)) {
                    $wall[] = BrickFactory::text(implode(' ', $buffer));
                }

                $wall[] = BrickFactory::equation($item);
                $buffer = [];
            } else {
                $buffer[] = $item;
            }
        }

        if (!empty($buffer)) {
            $wall[] = BrickFactory::text(implode(' ', $buffer));
        }

        return $wall;
    }
}
