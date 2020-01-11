<?php

declare(strict_types=1);

namespace Tests\API;

use Tests\APIStatelessTestCase;
use function base_path;

/**
 * Test CORS requests
 */
class CorsTest extends APIStatelessTestCase
{
    /**
     * Provide dataset for CORS tests.
     *
     * @return array
     */
    public function corsDataProvider(): array
    {
        return [
            ['dev', 'GET', 'http://localhost:4200', 204],
            ['dev', 'POST', 'http://localhost:4200', 204],
            ['dev', 'PATCH', 'http://localhost:4200', 204],
            ['dev', 'DELETE', 'http://localhost:4200', 204],

            ['dev', 'GET', 'https://dev.mathrix.fr', 204],
            ['dev', 'POST', 'https://dev.mathrix.fr', 204],
            ['dev', 'PATCH', 'https://dev.mathrix.fr', 204],
            ['dev', 'DELETE', 'https://dev.mathrix.fr', 204],

            ['master', 'GET', 'http://localhost:4200', 403],
            ['master', 'POST', 'http://localhost:4200', 403],
            ['master', 'PATCH', 'http://localhost:4200', 403],
            ['master', 'DELETE', 'http://localhost:4200', 403],

            ['master', 'GET', 'https://mathrix.fr', 204],
            ['master', 'POST', 'https://mathrix.fr', 204],
            ['master', 'PATCH', 'https://mathrix.fr', 204],
            ['master', 'DELETE', 'https://mathrix.fr', 204],
        ];
    }

    /**
     * Make an OPTIONS request (does not exist on Lumen < 6).
     *
     * @param string $uri
     * @param array  $data
     * @param array  $headers
     *
     * @return $this
     */
    public function options(string $uri, array $data = [], array $headers = []): self
    {
        $server = $this->transformHeadersToServerVars($headers);

        $this->call('OPTIONS', $uri, $data, [], [], $server);

        return $this;
    }

    /**
     * Test that CORS works.
     *
     * @param string $env            The environment.
     * @param string $method         The requested method.
     * @param string $origin         The origin.
     * @param int    $expectedStatus Th expected CORS result as HTTP code.
     *
     * @dataProvider corsDataProvider
     */
    public function testCors(string $env, string $method, string $origin, int $expectedStatus): void
    {
        $_ENV['APP_ENV'] = $env;

        // Override CORS configuration based on the app environment.
        /** @noinspection PhpIncludeInspection */
        $this->app->make('config')->set('cors', require base_path('config/cors.php'));

        // Craft the CORS request.
        $this->options('/countries', [], [
            'Origin'                        => $origin,
            'Access-Control-Request-Method' => $method,
        ]);

        $this->assertResponseStatus($expectedStatus);
    }
}
