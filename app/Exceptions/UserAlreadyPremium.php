<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;
use function trans;

/**
 * Thrown when a user tries to subscribe to a plan when he is already premium.
 */
class UserAlreadyPremium extends Http400BadRequestException
{
    public function __construct(User $user, ?Throwable $previous = null)
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();

        if ($currentUser !== null && $user->is($currentUser)) {
            $message = trans('users.you_already_premium');
        } else {
            $message = trans('users.user_already_premium', ['id' => $user->id]);
        }

        parent::__construct(
            ['user_id' => $user->id],
            $message,
            $previous
        );
    }
}
