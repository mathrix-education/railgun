<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Scalars;

use MLL\GraphQLScalars\Regex;

/**
 * References a hexadecimal color.
 * Backported from https://github.com/mll-lab/graphql-php-scalars.
 */
class Color extends Regex
{
    public $description = <<<'DESCRIPTION'
    A hexadecimal color is specified with: `#RRGGBB`, where `RR` (red), `GG` (green) and `BB` (blue)
    are hexadecimal integers between `00` and `FF` specifying the intensity of the color.
    DESCRIPTION;

    public static function regex(): string
    {
        return '/^#([a-f0-9]{6}|[a-f0-9]{3})$/';
    }
}
