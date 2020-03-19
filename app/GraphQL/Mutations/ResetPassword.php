<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Contracts\Container\BindingResolutionException;
use Railgun\Crypto\PasswordResetCrypto;
use Railgun\Models\User;
use function config;

class ResetPassword
{
    private PasswordResetCrypto $crypto;

    public function __construct(PasswordResetCrypto $crypto)
    {
        $this->crypto = $crypto;
    }

    /**
     * @param null    $rootValue
     * @param mixed[] $args
     *
     * @return mixed
     *
     * @throws BindingResolutionException
     */
    public function __invoke($rootValue, array $args)
    {
        $token = $args['token'];

        if (config('app.debug')) {
            // In debug mode, for testing purposes, passing the user id instead of the token allow to select the
            // targeted user.
            $user = User::query()->findOrFail($token);
        } else {
            $user = $this->crypto->check($token);
        }

        // Everything is correct, we can change the password without any furthermore verifications
        $user->password = $args['newPassword'];
        $user->save();

        return true;
    }
}
