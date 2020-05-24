<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Scalars;

use Carbon\Carbon;
use Nuwave\Lighthouse\Schema\Types\Scalars\DateScalar;

/**
 * Alternative to {@see \Nuwave\Lighthouse\Schema\Types\Scalars\DateTime} which uses IS0 8601 format.
 */
class DateTime extends DateScalar
{
    /**
     * @param Carbon $carbon The datetime instance.
     *
     * @return string The date as a string.
     */
    protected function format(Carbon $carbon): string
    {
        return $carbon->toIso8601ZuluString();
    }

    /**
     * @param mixed $value The date as a string.
     *
     * @return Carbon The parsed datetime instance.
     */
    protected function parse($value): Carbon
    {
        return Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $value);
    }
}
