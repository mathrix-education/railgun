<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Hash;
use JsonException;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Exceptions\InvalidCredentials;
use Railgun\Models\User;

/**
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
     * @param null           $rootValue
     * @param mixed[]        $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = User::query()
            ->where('email', '=', $args['email'])
            ->get(['id', 'password', 'scopes'])
            ->first();

        if ($user === null) {
            throw new InvalidCredentials();
        }

        $logged = Hash::check($args['password'], $user->password);

        if (!$logged) {
            throw new InvalidCredentials();
        }

        // Sign the JWS
        return [
            'token' => $this->driver->sign($user),
        ];
    }
}
