<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\User;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

/**
 * Thrown when a user is not a valid Facebook user.
 */
class InvalidFacebookUser extends Http400BadRequestException
{
    public function __construct(User $user, ?Throwable $previous = null)
    {
        parent::__construct(
            ['user_id' => $user->id],
            "User id `{$user->id}` is not a Facebook user",
            $previous
        );
    }
}
