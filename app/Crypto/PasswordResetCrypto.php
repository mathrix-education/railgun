<?php

declare(strict_types=1);

namespace Railgun\Crypto;

use Illuminate\Contracts\Container\BindingResolutionException;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Checker\MissingMandatoryClaimException;
use JsonException;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Mathrix\Lumen\JWT\Utils\JWTConfig;
use Railgun\Exceptions\InvalidAuthTokenException;
use Railgun\Models\User;
use function array_key_exists;
use function hash;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Handles all cryptographic operations related to password reset.
 */
class PasswordResetCrypto
{
    /**
     * Generate a JWT which will act as a secure password reset token.
     *
     * @param User $user The user who forgot his password.
     *
     * @return string The serialized JWT.
     *
     * @throws BindingResolutionException
     */
    public function get(User $user): string
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
            $json    = $driver->unserialize($token)->getPayload();
            $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (InvalidClaimException | MissingMandatoryClaimException | JsonException $exception) {
            // Catch any
            throw new InvalidAuthTokenException();
        }

        if (!array_key_exists('hsh', $payload) || !array_key_exists('sub', $payload)) {
            throw new InvalidAuthTokenException();
        }

        /** @var User $user */
        $user = User::query()->where('uuid', '=', $payload['sub'])->first();

        if (
            $user === null // User was not found
            || hash('sha256', $user->password) !== $payload['hsh'] // User password hash mismatch
        ) {
            throw new InvalidAuthTokenException();
        }

        return $user;
    }
}
