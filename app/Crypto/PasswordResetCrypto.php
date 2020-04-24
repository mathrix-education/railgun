<?php

declare(strict_types=1);

namespace Railgun\Crypto;

use Illuminate\Contracts\Container\BindingResolutionException;
use JsonException;
use Railgun\Exceptions\InvalidAuthTokenException;
use Railgun\Models\User;
use Windy\Guardian\Exceptions\InvalidClaimException;
use Windy\Guardian\Exceptions\InvalidSignatureException;
use Windy\Guardian\Guardian;
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
        return Guardian::get('reset')->sign([
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
        $authority = Guardian::get('reset');
        try {
            $authority->verify($token, true);
            $authority->check($token, true);
            $json    = $authority->unserialize($token)->getPayload();
            $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (InvalidSignatureException | InvalidClaimException | JsonException $exception) {
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
