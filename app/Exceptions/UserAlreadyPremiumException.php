<?php

namespace App\Exceptions;

use App\Models\User;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Throwable;

/**
 * Class UserAlreadyPremium.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class UserAlreadyPremiumException extends Http400BadRequestException
{
    protected const ERROR = "user_already_premium";


    public function __construct(User $user, Throwable $previous = null)
    {
        parent::__construct(["user_id" => $user->id], "User id `{$user->id}` is already premium", $previous);
    }
}
