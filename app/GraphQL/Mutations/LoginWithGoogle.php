<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Google_Client;
use Illuminate\Support\Str;
use Railgun\Exceptions\InvalidAuthTokenException;
use Railgun\Models\User;
use Throwable;
use Windy\Guardian\Guardian;
use function array_key_exists;
use function array_merge_recursive;

/**
 * Mutation loginWithGoogle.
 * Allow users to login using their Google account.
 */
class LoginWithGoogle
{
    private Google_Client $client;

    public function __construct(Google_Client $client)
    {
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
        if (Str::startsWith($args['accessToken'], 'ey')) {
            // This is an id token
            $idToken = $args['accessToken'];
        } else {
            // This is an access token
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($args['accessToken']);

            if (!array_key_exists('id_token', $accessToken)) {
                throw new InvalidAuthTokenException('Google');
            }

            $idToken = $accessToken['id_token'];
        }

        try {
            $payload = $this->client->verifyIdToken($idToken);
        } catch (Throwable $exception) {
            $payload = false;
        }

        if (!$payload) {
            throw new InvalidAuthTokenException('Google');
        }

        /** @var User $user */
        $user = User::query()
            ->where('metadata->google->id', '=', $payload['sub'])
            ->orWhere('email', '=', $payload['email'])
            ->first();

        if ($user === null) {
            $user              = new User();
            $user->email       = $payload['email'];
            $user->username    = $payload['name'];
            $user->firstname   = $payload['given_name'];
            $user->lastname    = $payload['family_name'];
            $user->permissions = [];
            $user->metadata    = array_merge_recursive($user->metadata ?? [], [
                'google' => [
                    'id' => $payload['sub'],
                ],
            ]);

            $user->save();
        }

        return [
            'token' => Guardian::sign([
                'sub' => (string)$user->getKey(),
                'scp' => $user->permissions,
            ]),
        ];
    }
}
