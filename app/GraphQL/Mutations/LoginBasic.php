<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Railgun\Exceptions\InvalidCredentialsException;
use Railgun\Models\User;
use Windy\Guardian\Guardian;

/**
 * Mutation loginBasic.
 * Login with email/password credentials.
 */
class LoginBasic
{
    /**
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The mutation arguments.
     *
     * @return string[] The signed, serialized JWT for the user.
     */
    public function __invoke($rootValue, array $args): array
    {
        if (!Auth::attempt(Arr::only($args, ['email', 'password']))) {
            throw new InvalidCredentialsException();
        }

        /** @var User $user */
        $user = Auth::user();

        // Sign the JWS
        return [
            'token' => Guardian::sign([
                'sub' => (string)$user->getKey(),
                'scp' => $user->permissions,
            ]),
        ];
    }
}
