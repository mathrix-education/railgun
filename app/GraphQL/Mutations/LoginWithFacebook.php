<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Facebook\Exceptions\FacebookAuthenticationException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use GraphQL\Type\Definition\ResolveInfo;
use JsonException;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Exceptions\InvalidAuthToken;
use Railgun\Models\User;
use function array_merge_recursive;

/**
 * Login with a Facebook access token
 */
class LoginWithFacebook
{
    public const GET_ME_URL = '/me?fields=id,first_name,last_name,email';

    private Facebook $facebook;
    private Driver   $driver;

    public function __construct(Facebook $facebook, Driver $driver)
    {
        $this->facebook = $facebook;
        $this->driver   = $driver;
    }

    /**
     * @param null           $rootValue
     * @param mixed[]        $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return mixed
     *
     * @throws FacebookSDKException
     * @throws JsonException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $accessToken = $args['accessToken'];

        try {
            $response = $this->facebook->get(self::GET_ME_URL, $accessToken);
        } catch (FacebookAuthenticationException $authenticationException) {
            // Handle invalid token with standard GraphQL UI-friendly error
            throw new InvalidAuthToken('Facebook');
        }

        $graphUser = $response->getGraphUser();

        $user = User::query()
            ->where('metadata->facebook->id', '=', $graphUser->getId())
            ->orWhere('email', '=', $graphUser->getEmail())
            ->first();

        if ($user === null) {
            // User does not exists, this is a registration
            $user            = new User();
            $user->firstname = $graphUser->getFirstName();
            $user->lastname  = $graphUser->getLastName();
            $user->email     = $graphUser->getEmail();
            $user->metadata  = array_merge_recursive($user->metadata ?? [], [
                'facebook' => [
                    'id' => $graphUser->getId(),
                ],
            ]);

            $user->save();
        }

        return [
            'token' => $this->driver->sign($user),
        ];
    }
}
