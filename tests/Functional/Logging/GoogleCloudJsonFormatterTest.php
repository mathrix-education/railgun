<?php

declare(strict_types=1);

namespace Tests\Functional\Logging;

use DateTimeImmutable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JsonException;
use Psr\Log\LogLevel;
use Railgun\Exceptions\MissingPermissionException;
use Railgun\Logging\GoogleCloudJsonFormatter;
use Railgun\Middleware\CorrelationMiddleware;
use Tests\ApplicationTestCase;
use function array_map;
use function range;
use function uuid_create;

/**
 * @coversDefaultClass \Railgun\Logging\GoogleCloudJsonFormatter
 */
class GoogleCloudJsonFormatterTest extends ApplicationTestCase
{
    /**
     * Generate a Monolog record payload.
     *
     * @return mixed[] The generated Monolog record.
     */
    private function generateMonologRecord(): array
    {
        $exception = new MissingPermissionException(['test']);

        return [
            'datetime'   => new DateTimeImmutable(),
            'level_name' => LogLevel::DEBUG,
            'message'    => $exception->getMessage(),
            'context'    => [
                'exception' => $exception,
            ],
            'extra'      => [],
        ];
    }

    /** @covers ::format
     * @covers ::toGoogleCloudLogging
     *
     * @throws JsonException
     * @throws BindingResolutionException
     */
    public function testFormat(): void
    {
        $instance = new GoogleCloudJsonFormatter();
        $this->app->instance('request', Request::create('/', 'POST'));
        $this->app->instance('response', Response::create());
        $this->app->instance(CorrelationMiddleware::ABSTRACT, uuid_create());
        $record = $this->generateMonologRecord();

        $result = $instance->format($record);

        $this->assertJson($result);
    }

    /** @covers ::formatBatchJson
     * @covers ::toGoogleCloudLogging
     */
    public function testFormatBatchJson(): void
    {
        $instance = new GoogleCloudJsonFormatter();
        $this->app->instance('request', Request::create('/', 'POST'));
        $this->app->instance('response', Response::create());
        $records = array_map(fn() => $this->generateMonologRecord(), range(0, 2));

        $records[2]['context']['exception'] = new HttpResponseException(Response::create());

        $result = $instance->formatBatch($records);

        $this->assertJson($result);
    }
}
