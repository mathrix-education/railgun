<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL;

use Illuminate\Support\Str;
use JsonException;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequestsLumen as MakesGraphQLRequests;
use Railgun\Models\User;
use stdClass;
use Tests\BaseTestCase;
use function array_keys;
use function count;
use function data_get;
use function dump;
use function factory;
use function is_string;
use function json_decode;
use function json_encode;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class GraphQLIntegrationTestCase extends BaseTestCase
{
    use MakesGraphQLRequests;

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
     * Get the GraphQL response data.
     * Shortcut for getContent('data').
     *
     * @param string|null $path The data path.
     *
     * @return mixed
     */
    protected function getData(?string $path = null)
    {
        $keys = array_keys((array)$this->getContent('data'));

        if (count($keys) === 1 && !Str::startsWith($path, $keys[0])) {
            // Guess the query key this is a single one
            $path = "{$keys[0]}.{$path}";
        }

        return $this->getContent('data' . ($path ? ".$path" : ''));
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
     * Get the GraphQL response errors.
     * Shortcut for getContent('errors').
     *
     * @return mixed
     */
    protected function getErrors()
    {
        return $this->getContent('errors');
    }

    /**
     * Assert that there is no GraphQL errors.
     *
     * @noinspection JsonEncodingApiUsageInspection
     */
    protected function assertNoErrors(): void
    {
        $errors = $this->getErrors();
        $this->assertNull($errors, "Found errors: \n"
            . json_encode($errors, JSON_PRETTY_PRINT, 512));
    }

    /**
     * Assert that a particular GraphQL was thrown.
     *
     * @param string|null $expectedMessage  If not null, check the error message.
     * @param string|null $expectedCategory If not null, check the error category.
     */
    protected function assertError(
        ?string $expectedMessage = null,
        ?string $expectedCategory = null
    ): void
    {
        if ($expectedMessage !== null) {
            $this->assertEquals($expectedMessage, $this->getErrors()[0]->message);
        } else {
            $this->assertNotEmpty($this->getErrors());
        }

        if ($expectedCategory === null) {
            return;
        }

        $this->assertEquals($expectedCategory, $this->getErrors()[0]->extensions->category);
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
