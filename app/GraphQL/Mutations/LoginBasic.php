<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Support\Facades\Hash;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Railgun\Exceptions\InvalidCredentialsException;
use Railgun\Models\User;

/**
 * Mutation loginBasic.
 * Login with email/password credentials.
 */
class LoginBasic
{
    private Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The mutation arguments.
     *
     * @return string[] The signed, serialized JWT for the user.
     */
    public function __invoke($rootValue, array $args): array
    {
        /** @var User $user */
        $user = User::query()
            ->where('email', '=', $args['email'])
            ->get(['id', 'password', 'scopes'])
            ->first();

        if ($user === null) {
            throw new InvalidCredentialsException();
        }

        $logged = Hash::check($args['password'], $user->password);

        if (!$logged) {
            throw new InvalidCredentialsException();
        }

        // Sign the JWS
        return [
            'token' => $this->driver->sign([
                'sub' => (string)$user->getKey(),
                'scp' => $user->scopes,
            ]),
        ];
    }
}
