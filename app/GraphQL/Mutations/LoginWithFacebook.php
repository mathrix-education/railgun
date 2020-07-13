<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Facebook\Exceptions\FacebookAuthenticationException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Railgun\Exceptions\InvalidAuthTokenException;
use Railgun\Models\User;
use Windy\Guardian\Guardian;
use function array_merge_recursive;

/**
 * Mutation loginWithFacebook.
 * Allow users to login with their Facebook account.
 */
class LoginWithFacebook
{
    public const GET_ME_URL = '/me?fields=id,first_name,last_name,email';

    private Facebook $facebook;

    public function __construct(Facebook $facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The mutation arguments.
     *
     * @return mixed[] The signed, serialized JWT for the user.
     *
     * @throws FacebookSDKException
     *
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function __invoke($_, array $args): array
    {
        $accessToken = $args['accessToken'];

        try {
            $response = $this->facebook->get(self::GET_ME_URL, $accessToken);
        } catch (FacebookAuthenticationException $authenticationException) {
            throw new InvalidAuthTokenException('Facebook');
        }

        $graphUser = $response->getGraphUser();

        /** @var User $user */
        $user = User::query()
            ->where('metadata->facebook->id', '=', $graphUser->getId())
            ->orWhere('email', '=', $graphUser->getEmail())
            ->first();

        if ($user === null) {
            // User does not exists, this is a registration
            $user              = new User();
            $user->firstname   = $graphUser->getFirstName();
            $user->lastname    = $graphUser->getLastName();
            $user->email       = $graphUser->getEmail();
            $user->permissions = [];
            $user->metadata    = array_merge_recursive($user->metadata ?? [], [
                'facebook' => [
                    'id' => $graphUser->getId(),
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
