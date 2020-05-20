<?php

declare(strict_types=1);

namespace Tests\Functional\Logging;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Railgun\Logging\HttpRequestLogEntry;
use Tests\ApplicationTestCase;
use function microtime;

/**
 * @coversDefaultClass \Railgun\Logging\HttpRequestLogEntry
 */
class HttpRequestLogEntryTest extends ApplicationTestCase
{
    /**
     * @covers ::__construct
     * @covers ::toArray
     *
     * @throws BindingResolutionException
     */
    public function testToArray(): void
    {
        $request = Request::create('/foo', 'PATCH');
        $request->server->set('REQUEST_TIME_FLOAT', microtime(true));
        $request->headers->set('User-Agent', 'PHPUnit');
        $response = new Response('foo', 201);

        $instance = new HttpRequestLogEntry($request, $response);
        $data     = $instance->toArray();

        $this->assertEquals('PATCH', $data['requestMethod']);
        $this->assertEquals('PHPUnit', $data['userAgent']);
    }

    /**
     * @covers ::__construct
     * @covers ::toArray
     */
    public function assertIgnoreDefaultRequest(): void
    {
        $this->assertNull((new HttpRequestLogEntry())->toArray());
    }
}
