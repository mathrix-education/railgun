<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL;

use Illuminate\Support\Str;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequestsLumen as MakesGraphQLRequests;
use Tests\E2E\HttpIntegrationTestCase;
use function array_keys;
use function count;
use function json_encode;
use const JSON_PRETTY_PRINT;

class GraphQLTestCase extends HttpIntegrationTestCase
{
    use MakesGraphQLRequests;

    /**
     * @param string $expectedPattern The expected error message pattern.
     */
    public function assertErrorMessageMatches(string $expectedPattern): void
    {
        $this->assertMatchesRegularExpression($expectedPattern, $this->getContent('errors.0.extensions.message'));
    }

    /**
     * @param string $expectedMessage The expected error message.
     */
    public function assertErrorMessage(string $expectedMessage): void
    {
        $this->assertEquals($expectedMessage, $this->getContent('errors.0.message'));
    }

    /**
     * @param string $expectedCode The expected error code.
     */
    public function assertErrorCode(string $expectedCode): void
    {
        $this->assertEquals($expectedCode, $this->getContent('errors.0.extensions.code'));
    }

    /**
     * @param string $expectedCategory The expected error category.
     */
    public function assertErrorCategory(string $expectedCategory): void
    {
        $this->assertEquals($expectedCategory, $this->getContent('errors.0.extensions.category'));
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
     * Get the GraphQL response errors.
     * Shortcut for getContent('errors').
     *
     * @param string|null $path The error path.
     *
     * @return mixed
     */
    protected function getErrors(?string $path = null)
    {
        if (!Str::startsWith($path, '0.')) {
            $path = "0.$path";
        }

        return $this->getContent('errors' . ($path ? ".$path" : ''));
    }

    /**
     * Assert that there is no GraphQL errors.
     *
     * @noinspection JsonEncodingApiUsageInspection
     */
    protected function assertNoErrors(): void
    {
        $errors = $this->getContent('errors');
        $this->assertNull($errors, "Expected no errors, but found errors: \n"
            . json_encode($errors, JSON_PRETTY_PRINT, 512));
    }
}
