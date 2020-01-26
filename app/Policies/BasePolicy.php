<?php

declare(strict_types=1);

namespace Railgun\Policies;

use Illuminate\Support\Str;
use function in_array;

/**
 * Allow standard REST routes without any modifiers on users and model. This trait is useful when permissions are
 * directly handled by middleware.
 */
abstract class BasePolicy
{
    public function __call($name, $arguments)
    {
        $isStandard = in_array($name, ['index', 'post', 'get', 'patch', 'delete']);
        $isRelation = Str::startsWith($name, 'by');

        return $isStandard || $isRelation;
    }
}
