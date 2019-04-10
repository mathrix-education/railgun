<?php

namespace App\Policies;

use Illuminate\Support\Str;

/**
 * Class BasePolicy.
 * Allow standard REST routes without any modifiers on users and model. This trait is useful when permissions are
 * directly handled by middleware.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
abstract class BasePolicy
{
    public function __call($name, $arguments)
    {
        if (in_array($name, ["index", "post", "get", "patch", "delete"])) {
            return true;
        } else if (Str::startsWith($name, "by")) {
            return true;
        } else {
            return false;
        }
    }
}
