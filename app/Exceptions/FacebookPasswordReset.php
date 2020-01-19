<?php

declare(strict_types=1);

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequest;
use Throwable;
use function trans;

/**
 * Thrown when a user which account has been created through Facebook tries to reset his password.
 */
class FacebookPasswordReset extends Http400BadRequest
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct([], trans('users.facebook_password_reset'), $previous);
    }
}
