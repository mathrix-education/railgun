<?php

declare(strict_types=1);

namespace Railgun\Buster\Utils;

use function array_map;
use function explode;
use function implode;
use function trim;

class StringUtils
{
    /**
     * Trim a multiline string.
     */
    public static function mtrim(string $str): string
    {
        $strs = explode("\n", $str);
        $strs = array_map(static function ($str) {
            return trim($str);
        }, $strs);
        $str  = implode("\n", $strs);

        return trim($str);
    }
}
