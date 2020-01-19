<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Hash;
use Mathrix\Lumen\JWT\Auth\Exceptions\InvalidCredentials;
use Mathrix\Lumen\JWT\Auth\JWT\JWTIssuer;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Login
{
    /**
     * @param                     $rootValue
     * @param array               $args
     * @param GraphQLContext|null $context
     * @param ResolveInfo         $resolveInfo
     */
    public function resolve($rootValue, array $args, ?GraphQLContext $context, ResolveInfo $resolveInfo)
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
