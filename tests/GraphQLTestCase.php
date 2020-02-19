<?php

declare(strict_types=1);

namespace Tests;

use JsonException;
use stdClass;
use const JSON_THROW_ON_ERROR;
use function config;
use function data_get;
use function json_decode;

class GraphQLTestCase extends BaseTestCase
{
    /** @var stdClass|array $contentCache */
    private $contentCache;

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

        $this->json('post', $this->getGraphQLEndpoint(), $data, $headers);
    }

    protected function getGraphQLEndpoint(): string
    {
        return config('lighthouse.route.uri');
    }

    /**
     * Get the GraphQL response data.
     * Shortcut for getContent('data').
     *
     * @return mixed
     */
    protected function getData()
    {
        return $this->getContent('data');
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
            } /** @noinspection PhpUndefinedClassInspection */ catch (JsonException $exception) {
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
}
