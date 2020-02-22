<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Google_Client;
use GraphQL\Type\Definition\ResolveInfo;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Exceptions\InvalidCredentials;
use Railgun\Models\User;

class LoginWithGoogle
{
    private Driver           $driver;
    private Google_Client   $client;

    public function __construct(Driver $driver, Google_Client $client)
    {
        $this->driver = $driver;
        $this->client = $client;
    }

    /**
     * @param null           $rootValue
     * @param mixed[]        $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $payload = $this->client->verifyIdToken($args['access_token']);

        if (!$payload) {
            throw new InvalidCredentials();
        }

        $user = User::query()
            ->where('metadata->google->id', '=', $payload['sub'])
            ->get(['id', 'password', 'scopes'])
            ->first();

        if ($user === null) {
            $user            = new User();
            $user->email     = $payload['email'];
            $user->username  = $payload['name'];
            $user->firstname = $payload['given_name'];
            $user->lastname  = $payload['family_name'];
            $user->save();
        }

        return [
            'token' => $this->driver->sign($user),
        ];
    }
}
