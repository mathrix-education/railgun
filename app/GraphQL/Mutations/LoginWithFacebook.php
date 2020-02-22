<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use GraphQL\Type\Definition\ResolveInfo;
use JsonException;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\User;
use function array_merge_recursive;

/**
 * Login with a Facebook access token
 */
class LoginWithFacebook
{
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
        $response    = $this->facebook->get('/me?fields=id,first_name,last_name,email', $accessToken);
        $graphUser   = $response->getGraphUser();

        $user = User::query()
            ->where('metadata->facebook->id', '=', $graphUser->getId())
            ->first();

        if ($user === null) {
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
