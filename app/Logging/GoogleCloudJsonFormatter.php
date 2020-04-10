<?php

declare(strict_types=1);

namespace Railgun\Logging;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use JsonException;
use Monolog\DateTimeImmutable;
use Monolog\Formatter\JsonFormatter;
use Railgun\Middleware\CorrelationMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use function app;
use function array_key_exists;
use function array_map;
use function array_merge;
use function collect;
use function get_class;
use function json_encode;
use function mb_strlen;
use function microtime;
use function str_replace;
use const JSON_THROW_ON_ERROR;

/**
 * Format a log entry to the Google Cloud LogEntry format
 *
 * @link https://cloud.google.com/logging/docs/reference/v2/rest/v2/LogEntry
 */
class GoogleCloudJsonFormatter extends JsonFormatter
{
    /**
     * {@inheritdoc}
     *
     * @throws JsonException
     */
    public function format(array $record): string
    {
        return json_encode($this->toGoogleCloudLogging($record), JSON_THROW_ON_ERROR, 512)
            . ($this->appendNewline ? "\n" : '');
    }

    /**
     * Return a JSON-encoded array of records.
     *
     * @param mixed[] $records The Monolog records.
     *
     * @return string The encoded Monolog records.
     *
     * @throws JsonException
     */
    protected function formatBatchJson(array $records): string
    {
        $records = array_map(
            fn($record) => $this->toGoogleCloudLogging($record),
            $records,
        );

        return json_encode($records, JSON_THROW_ON_ERROR, 512);
    }

    /**
     * Cast a Monolog record to a Google Cloud Logging-compatible record.
     *
     * @link https://cloud.google.com/logging/docs/reference/v2/rest/v2/LogEntry
     *
     * @param mixed[] $record The Monolog record.
     *
     * @return mixed[] The record as a Google Cloud LogEntry.
     */
    protected function toGoogleCloudLogging(array $record): array
    {
        /** @var DateTimeImmutable $date */
        $date = $record['datetime'];

        // Initialize the payload
        $payload = [
            'severity'    => $record['level_name'],
            'timestamp'   => [
                'seconds' => $date->getTimestamp(),
                'nanos'   => 0,
            ],
            'jsonPayload' => [
                'message' => $record['message'],
            ],
        ];

        // Include the httpRequest field if applicable
        $httpRequest = $this->getHttpRequest();
        if ($httpRequest !== null) {
            $payload['httpRequest'] = $httpRequest;
        }

        // Include the correlation id if applicable
        if (app()->bound(CorrelationMiddleware::ABSTRACT)) {
            $payload['correlation'] = app()->make(CorrelationMiddleware::ABSTRACT);
        }

        // Include the embedded exception if applicable
        if (
            array_key_exists('exception', $record['context'])
            && $record['context']['exception'] instanceof Throwable
        ) {
            /** @var Throwable $exception */
            $exception = $record['context']['exception'];

            $payload = array_merge($payload, [
                'message'   => $exception->getMessage(),
                'exception' => get_class($exception),
                'file'      => $exception->getFile(),
                'line'      => $exception->getLine(),
                'trace'     => collect($exception->getTrace())
                    ->map(static fn($trace) => Arr::except($trace, ['args']))
                    ->all(),
            ]);

            // Inject the status code
            if ($exception instanceof HttpException) {
                $payload['httpRequest']['status'] = $exception->getStatusCode();
            } elseif ($exception instanceof HttpResponseException) {
                $payload['httpRequest']['status'] = $exception->getResponse()->getStatusCode();
            }

            unset($record['context']['exception']);
        }

        $payload = array_merge($record['context'], $record['extra'], $payload);

        return $payload;
    }

    /**
     * Format the Lumen request to a Google Cloud Logging-compatible httpRequest entry.
     *
     * @link https://cloud.google.com/logging/docs/reference/v2/rest/v2/LogEntry#httprequest
     *
     * @return mixed[] The Google Cloud LogEntry httpRequest field as an array.
     */
    private function getHttpRequest(): array
    {
        if (!app()->bound('request')) {
            return []; // @codeCoverageIgnore
        }

        /** @var Request $request */
        $request = app()->make('request');

        if ($request === null) {
            return []; // @codeCoverageIgnore
        }

        return [
            'requestMethod' => $request->getMethod(),
            'requestUrl'    => str_replace($request->getBaseUrl(), '', $request->fullUrl()),
            'requestSize'   => mb_strlen($request->getContent(), '8bit'),
            'status'        => 200,
            'userAgent'     => $request->userAgent(),
            'remoteIp'      => $request->getClientIp(),
            'referer'       => $request->header('Referer'),
            'latency'       => (microtime(true) - (float)$request->server('REQUEST_TIME_FLOAT')) . 's',
            'protocol'      => $request->getProtocolVersion(),
        ];
    }
}
