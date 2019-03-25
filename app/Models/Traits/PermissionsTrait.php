<?php

namespace App\Models\Traits;

use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;

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
     * Check if user is redactor.
     *
     * @return bool
     */
    public function isRedactor()
    {
        return $this->isModerator() || $this->tokenCan("redactor");
    }


    /**
     * Check if user is at least moderator.
     *
     * @return bool
     */
    public function isModerator()
    {
        return $this->isAdmin() || $this->tokenCan("moderator");
    }


    /**
     * Check if user is administrator.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->tokenCan("admin");
    }


    /**
     * Check if user is administrator.
     *
     * @return bool
     */
    public function isSupport()
    {
        return $this->isAdmin() || $this->tokenCan("support");
    }


    /**
     * Check if user is authorized to do something; if not throw an exception.
     *
     * @see Authorizable
     *
     * @param string $ability
     * @param array $arguments
     * @param string $exceptionClass
     */
    public function canOrFail($ability, $arguments = [], $exceptionClass = Http401UnauthorizedException::class)
    {
        if ($this->cant($ability, $arguments) && class_exists($exceptionClass)) {
            throw new $exceptionClass();
        }
    }
}
