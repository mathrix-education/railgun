<?php

declare(strict_types=1);

namespace Railgun\Logging;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Monolog\Formatter\JsonFormatter;
use const JSON_THROW_ON_ERROR;
use function app;
use function array_map;
use function array_merge;
use function json_encode;
use function mb_strlen;

/**
 * Monolog Formatter for Google Cloud Run output.
 *
 * @codeCoverageIgnore
 */
class StackdriverFormatter extends JsonFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record): string
    {
        return json_encode($this->toStackdriverFormat($record), JSON_THROW_ON_ERROR, 512)
            . ($this->appendNewline ? "\n" : '');
    }

    /**
     * Return a JSON-encoded array of records.
     *
     * @param array $records
     *
     * @return string
     */
    protected function formatBatchJson(array $records): string
    {
        $records = array_map(
            function ($record) {
                return $this->toStackdriverFormat($record);
            },
            $records
        );

        return json_encode($records, JSON_THROW_ON_ERROR, 512);
    }

    /**
     * Transform a record to a Stackdriver-compatible format.
     *
     * @param array $record The raw record.
     *
     * @return array
     */
    protected function toStackdriverFormat(array $record): array
    {
        /** @var Request $request */
        $request = app(Request::class);
        /** @var Response $response */
        $response = app(Response::class);

        $dt = $record['datetime'];

        $formatted = [
            'message'     => $record['message'],
            'severity'    => $record['level_name'],
            'timestamp'   => [
                'seconds' => $dt->getTimestamp(),
                'nanos'   => 0,
            ],
            'channel'     => $record['channel'],
            'httpRequest' => [
                'requestMethod' => $request->getMethod(),
                'requestUrl'    => $request->getUri(),
                'requestSize'   => (string)mb_strlen($request->__toString(), '8bit'),
                'status'        => $response->getStatusCode(),
                'responseSize'  => (string)mb_strlen($response->__toString(), '8bit'),
                'userAgent'     => $request->headers->get('User-Agent'),
                'protocol'      => $response->getProtocolVersion(),
            ],
        ];

        $formatted = array_merge($record['context'], $record['extra'], $formatted);

        return $formatted;
    }
}
