<?php

declare(strict_types=1);

namespace Tests\E2E;

use JsonException;
use Railgun\Models\User;
use stdClass;
use Tests\ApplicationTestCase;
use function data_get;
use function dump;
use function factory;
use function is_string;
use function json_decode;
use const JSON_THROW_ON_ERROR;

class HttpIntegrationTestCase extends ApplicationTestCase
{
    /** @var stdClass|mixed[] $contentCache The JSON-decoded content. */
    private $contentCache;

    /**
     * Shortcut to create a new pristine user from the User factory.
     *
     * @param mixed[]|string $attributes The overridden attributes (array) or the permission to use (string).
     *
     * @return User The new user.
     */
    protected function actingAsNewUser($attributes = []): User
    {
        if (is_string($attributes)) {
            $attributes = ['scopes' => [$attributes]];
        }

        /** @var User $user */
        $user = factory(User::class)->create($attributes);
        $this->actingAs($user);

        return $user;
    }

    /**
     * Get an arbitrary part of the response data.
     *
     * @param string $path    The path to get.
     * @param null   $default The default value, if any.
     *
     * @return mixed
     */
    protected function getContent(?string $path = null, $default = null)
    {
        if (!isset($this->contentCache)) {
            try {
                $this->contentCache = json_decode($this->response->getContent(), false, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                return $default;
            }
        }

        return data_get($this->contentCache, $path, $default);
    }

    /**
     * Dump the current response JSON content.
     */
    protected function dump(): void
    {
        $content = $this->getContent();
        /** @noinspection ForgottenDebugOutputInspection */
        dump($content);
        exit(1);
    }
}
