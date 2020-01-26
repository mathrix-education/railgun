<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Exams;

use Exception;
use Ramsey\Uuid\Uuid;
use function array_map;
use function explode;
use function implode;
use function trim;

class StringUtils
{
    /**
     * Get a random uuid v4 as a string.
     *
     * @return string|null
     */
    public static function uuid(): ?string
    {
        try {
            return Uuid::uuid4()->toString();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Trim a multiline string.
     *
     * @param string $str
     *
     * @return string
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
