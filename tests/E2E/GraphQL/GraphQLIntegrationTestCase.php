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

class GraphQLIntegrationTestCase extends HttpIntegrationTestCase
{
    use MakesGraphQLRequests;

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
}
