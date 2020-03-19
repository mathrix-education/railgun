<?php

declare(strict_types=1);

namespace Railgun\Crypto;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Mathrix\Lumen\JWT\Utils\JWTConfig;
use Railgun\Exceptions\InvalidAuthToken;
use Railgun\Models\User;
use const JSON_THROW_ON_ERROR;
use function array_key_exists;
use function hash;
use function json_decode;

class PasswordResetCrypto
{
    public function get(User $user)
    {
        $driver = Driver::from(JWTConfig::key('reset'), JWTConfig::payload('reset'));

        return $driver->sign([
            'sub' => $user->uuid,
            'hsh' => hash('sha256', $user->password),
        ]);
    }

    /**
     * Check a password reset JWT and returns the corresponding user.
     *
     * @param string $token The password reset JWT.
     *
     * @return User The resolved user.
     *
     * @throws BindingResolutionException
     */
    public function check(string $token): User
    {
        $driver = Driver::from(JWTConfig::key('reset'), JWTConfig::payload('reset'));
        try {
            $driver->verify($token);
            $driver->check($token);
            $payload = json_decode($driver->unserialize($token)->getPayload(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            throw new InvalidAuthToken();
        }

        if (!array_key_exists('hsh', $payload) || !array_key_exists('sub', $payload)) {
            throw new InvalidAuthToken();
        }

        /** @var User $user */
        $user = User::query()->where('uuid', '=', $payload['sub'])->first();

        if ($user === null // User was not found
            || hash('sha256', $user->password) !== $payload['hsh'] // User password hash mismatch
        ) {
            throw new InvalidAuthToken();
        }

        return $user;
    }
}
