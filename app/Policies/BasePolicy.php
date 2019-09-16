<?php

declare(strict_types=1);

namespace App\Policies;

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
        if (in_array($name, ['index', 'post', 'get', 'patch', 'delete'])) {
            return true;
        }

        if (Str::startsWith($name, 'by')) {
            return true;
        }

        return false;
    }
}
