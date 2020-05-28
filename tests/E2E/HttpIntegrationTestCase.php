<?php

declare(strict_types=1);

namespace Tests\E2E;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use JsonException;
use Railgun\Models\User;
use stdClass;
use Tests\ApplicationTestCase;
use function array_merge;
use function array_unique;
use function dump;
use function factory;
use function is_array;
use function json_decode;
use const JSON_THROW_ON_ERROR;

class HttpIntegrationTestCase extends ApplicationTestCase
{
    /** @var stdClass|mixed[] $contentCache The JSON-decoded content. */
    private $contentCache;

    /**
     * @param Authenticatable|User|mixed[] $user  The user or the user attributes.
     * @param string|null                  $guard The guard to use, use the default one if null is provided.
     *
     * @return $this
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function actingAs($user = [], $guard = null): self
    {
        if (is_array($user)) {
            $user = factory(User::class)->create($user);
        }

        return parent::actingAs($user);
    }

    /**
     * Set the currently logged user and returns it.
     *
     * @param Authenticatable|User|mixed[] $user  The user or the user attributes.
     * @param string|null                  $guard The guard to use, use the default one if null is provided.
     *
     * @return User The currently logged user.
     */
    public function actingAsGetUser($user = [], ?string $guard = null): User
    {
        $this->actingAs($user, $guard);

        return $this->app->make('auth')->guard($guard)->user();
    }

    /**
     * @param string|string[]|null $permissions The permissions to grant to the user.
     * @param string|null          $guard       The guard to use, use the default one if null is provided.
     *
     * @return User The application user.
     */
    protected function withPermissions($permissions = null, ?string $guard = null): User
    {
        if ($permissions === null) {
            $permissions = [];
        } elseif (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        /** @var User $user The currently logged user. */
        $user = $this->app->make('auth')->guard($guard)->user() ?? factory(User::class)->create();

        $user->scopes = array_unique(array_merge($user->scopes, $permissions));
        $user->save();

        return $this->actingAsGetUser($user);
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
                $this->contentCache = json_decode($this->response->getContent(), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                return $default;
            }
        }

        return Arr::get($this->contentCache, $path, $default);
    }

    /**
     * Dump the current response JSON content.
     */
    protected function dump(): void
    {
        $this->tearDown();
        self::tearDownAfterClass();
        $content = $this->getContent();
        /** @noinspection ForgottenDebugOutputInspection */
        dump($content);
        exit(1);
    }
}
