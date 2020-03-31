<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Scalars;

use Illuminate\Support\Facades\Validator;
use MLL\GraphQLScalars\StringScalar;

/**
 * GraphQL Scalar which represents an URL.
 */
class URL extends StringScalar
{
    /**
     * Validate the url against the Laravel url validation rule.
     *
     * @link https://laravel.com/docs/7.x/validation#rule-url
     *
     * @param string $url The given URL.
     *
     * @return bool If the url is valid.
     */
    protected function isValid(string $url): bool
    {
        return Validator::make(['url' => $url], ['url' => 'required|url'])->passes();
    }
}
