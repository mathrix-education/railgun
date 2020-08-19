<?php

declare(strict_types=1);

namespace Railgun\Logging;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Exceptions\HttpResponseException;
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
use function get_class;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * Format a log entry to the Google Cloud LogEntry format
 *
 * @link https://cloud.google.com/logging/docs/reference/v2/rest/v2/LogEntry
 */
class GoogleCloudJsonFormatter extends JsonFormatter
{
    public const NO_MESSAGE = 'no-message';

    /**
     * {@inheritdoc}
     *
     * @throws BindingResolutionException
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
     * @throws BindingResolutionException
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
     *
     * @throws BindingResolutionException
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
                'nanos'   => (int)$date->format('u') * 1000,
            ],
            'httpRequest' => (new HttpRequestLogEntry())->toArray(),
            'message'     => $record['message'],
        ];

        if ($payload['message'] === self::NO_MESSAGE) {
            unset($payload['message']); // @codeCoverageIgnore
        }

        if ($payload['httpRequest'] === null) {
            unset($payload['httpRequest']);
        }

        // Include the correlation id if applicable
        if (app()->bound(CorrelationMiddleware::ABSTRACT)) {
            $payload['correlation'] = Container::getInstance()->make(CorrelationMiddleware::ABSTRACT);
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
                'trace'     => ExceptionTrace::dump($exception),
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
}
