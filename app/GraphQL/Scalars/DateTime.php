<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Scalars;

use Carbon\Carbon;
use Nuwave\Lighthouse\Schema\Types\Scalars\DateScalar;
use Throwable;

/**
 * Alternative to {@see \Nuwave\Lighthouse\Schema\Types\Scalars\DateTime} which uses RFC3339 format.
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
        return $carbon->toRfc3339String();
    }

    /**
     * @param mixed $value The date as a RFC3339 string.
     *
     * @return Carbon The parsed datetime instance.
     */
    protected function parse($value): Carbon
    {
        try {
            return Carbon::createFromFormat(Carbon::RFC3339, $value);
        } catch (Throwable $exception) {
            return Carbon::createFromFormat(Carbon::RFC3339_EXTENDED, $value);
        }
    }
}
