<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Google_Client;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Railgun\Exceptions\InvalidAuthTokenException;
use Railgun\Models\User;

/**
 * Mutation loginWithGoogle.
 * Allow users to login using their Google account.
 */
class LoginWithGoogle
{
    private Driver        $driver;
    private Google_Client $client;

    public function __construct(Driver $driver, Google_Client $client)
    {
        $this->driver = $driver;
        $this->client = $client;
    }

    /**
     * @link https://developers.google.com/identity/sign-in/web/backend-auth#calling-the-tokeninfo-endpoint
     *
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The mutation arguments.
     *
     * @return mixed[] The signed, serialized JWT for the user.
     */
    public function __invoke($rootValue, array $args): array
    {
        $payload = $this->client->verifyIdToken($args['accessToken']);

        if (!$payload) {
            throw new InvalidAuthTokenException('Google');
        }

        /** @var User $user */
        $user = User::query()
            ->where('metadata->google->id', '=', $payload['sub'])
            ->orWhere('email', '=', $payload['email'])
            ->first();

        if ($user === null) {
            $user            = new User();
            $user->email     = $payload['email'];
            $user->username  = $payload['name'];
            $user->firstname = $payload['given_name'];
            $user->lastname  = $payload['family_name'];
            $user->scopes    = [];
            $user->save();
        }

        return [
            'token' => $this->driver->sign([
                'sub' => (string)$user->getKey(),
                'scp' => $user->scopes,
            ]),
        ];
    }
}
