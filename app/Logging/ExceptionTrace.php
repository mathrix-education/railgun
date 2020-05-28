<?php

declare(strict_types=1);

namespace Railgun\Logging;

use Throwable;
use function app;
use function array_key_exists;
use function collect;
use function get_class;
use function is_callable;
use function is_object;
use function is_scalar;
use function is_string;
use function method_exists;
use function str_replace;
use const DIRECTORY_SEPARATOR;

/**
 * @codeCoverageIgnore
 */
class ExceptionTrace
{
    /**
     * Get the trace of an exception.
     *
     * @param Throwable $exception The exception.
     * @param int       $limit     The trace limit size.
     *
     * @return string[]
     */
    public static function dump(Throwable $exception, int $limit = 50): array
    {
        return collect($exception->getTrace())
            ->take($limit)
            ->map(static function (array $line): string {
                if (array_key_exists('file', $line)) {
                    $location = str_replace(app()->basePath() . DIRECTORY_SEPARATOR, '', $line['file']);

                    if (array_key_exists('line', $line)) {
                        $location .= "({$line['line']})";
                    }
                } else {
                    $location = '[internal function]';
                }

                $code = ($line['class'] ?? '') . ($line['type'] ?? '') . ($line['function'] ?? '');

                $args = collect($line['args'] ?? [])
                    ->map(static function ($arg) {
                        if (is_callable($arg)) {
                            return 'Closure';
                        }

                        if (is_object($arg)) {
                            return method_exists($arg, '__toString')
                                ? (string)$arg
                                : get_class($arg);
                        }

                        if (is_string($arg)) {
                            return "\"$arg\"";
                        }

                        if (is_scalar($arg)) {
                            return $arg;
                        }

                        return 'unknown';
                    })
                    ->implode(',');

                return "$location: $code($args)";
            })
            ->toArray();
    }
}
