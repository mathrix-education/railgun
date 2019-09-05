<?php

namespace App\Models\Traits;

use Mathrix\Lumen\Zero\Exceptions\Http\Http401UnauthorizedException;

/**
 * Trait PermissionsTrait.
 * Describes all user permissions based on their token scope.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.1
 */
trait PermissionsTrait
{
    /**
     * Check if user is authorized to do something; if not throw an exception.
     *
     * @param string $ability
     * @param array $arguments
     *
     * @throws Http401UnauthorizedException
     * @see Authorizable
     */
    public function canOrFail($ability, $arguments = [])
    {
        if ($this->cant($ability, $arguments)) {
            throw new Http401UnauthorizedException(null, "Failed to pass $ability policy");
        }
    }
}
