<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Mathrix\Lumen\Zero\Exceptions\Http\Http401UnauthorizedException;

/**
 * Trait HasPermissions.
 * Describes all user permissions based on their token scope.
 */
trait HasPermissions
{
    /**
     * Check if user is authorized to do something; if not throw an exception.
     *
     * @see Authorizable
     *
     * @param string      $ability   The ability.
     * @param array|mixed $arguments The policy arguments.
     *
     * @throws Http401UnauthorizedException
     */
    public function canOrFail(string $ability, $arguments = []): void
    {
        if ($this->cant($ability, $arguments)) {
            throw new Http401UnauthorizedException(null, "Failed to pass $ability policy");
        }
    }
}
