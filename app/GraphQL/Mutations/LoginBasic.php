<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Hash;
use Mathrix\Lumen\JWT\Auth\Exceptions\InvalidCredentials;
use Mathrix\Lumen\JWT\Auth\JWT\JWTIssuer;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use function app;

class LoginBasic
{
    /**
     * Return a value for the field.
     *
     * @param null           $rootValue   Usually contains the result returned from the parent field. In this case, it
     *                                    is always `null`.
     * @param mixed[]        $args        The arguments that were passed into the field.
     * @param GraphQLContext $context     Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo    $resolveInfo Information about the query itself, such as the execution state, the field
     *                                    name, path to the field from the root, and more.
     *
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        /** @var JWTIssuer $jwtIssuer */
        $jwtIssuer = app()->make(JWTIssuer::class);

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
            'token' => $jwtIssuer->serializeJWS($jwtIssuer->issueJWS($user)),
        ];
    }
}
