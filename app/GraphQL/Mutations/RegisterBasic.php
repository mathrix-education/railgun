<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Railgun\Models\User;
use Windy\Guardian\Guardian;

/**
 * Mutation registerBasic.
 * Register a new user with email/password credentials.
 */
class RegisterBasic
{
    /**
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The mutation arguments (email, password).
     *
     * @return string[] The signed, serialized JWT for the user.
     */
    public function __invoke($rootValue, array $args): array
    {
        $user = new User($args);
        $user->save();

        // Sign the JWS
        return [
            'token' => Guardian::sign([
                'sub' => (string)$user->getKey(),
                'scp' => $user->scopes,
            ]),
        ];
    }
}
