<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL;

use JsonException;
use Railgun\Models\User;
use stdClass;
use Tests\BaseTestCase;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use function config;
use function data_get;
use function factory;
use function json_decode;
use function json_encode;

class GraphQLIntegrationTestCase extends BaseTestCase
{
    /** @var stdClass|array $contentCache */
    private $contentCache;

    /**
     * Shortcut to create a new pristine user from the User factory.
     *
     * @param array $attributes The overridden attributes.
     *
     * @return User The new user.
     */
    protected function actingAsNewUser(array $attributes = []): User
    {
        /** @var User $user */
        $user = factory(User::class)->create($attributes);
        $this->actingAs($user);

        return $user;
    }

    /**
     * Query the GraphQL API.
     *
     * @param string $query     The GraphQL query payload.
     * @param array  $variables The GraphQL query variables.
     * @param array  $headers   Additional HTTP headers.
     */
    protected function graphQL(string $query, array $variables = [], array $headers = []): void
    {
        $data = ['query' => $query];

        if ($variables) {
            $data['variables'] = $variables;
        }

        $this->json('post', config('lighthouse.route.uri'), $data, $headers);
    }

    /**
     * Get the GraphQL response data.
     * Shortcut for getContent('data').
     *
     * @param string|null $path
     *
     * @return mixed
     */
    protected function getData(?string $path = null)
    {
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
}
