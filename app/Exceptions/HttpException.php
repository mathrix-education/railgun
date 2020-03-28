<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;
use Throwable;
use function app;

/**
 * Base exception for GraphQL errors.
 */
abstract class HttpException extends SymfonyHttpException implements RendersErrorsExtensions
{
    /** @var string $level The PSR3 log level ({@see LogLevel} */
    protected string $level = LogLevel::ERROR;
    /** @var string $category The error category ({@see ClientAware} */
    protected string $category = 'internal';

    /**
     * Since GraphQL errors are considered as "normal" responses, we should not try to override the HTTP and return a
     * 200 OK status code.
     * The headers can be set using the {@see HttpException::setHeaders()} method if necessary but will be sent by
     * the Lighthouse framework. Moreover, we do not use exception code at the moment.
     *
     * @param string|null    $message  The user-friendly message to display.
     * @param Throwable|null $previous The underlying exception, if necessary.
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        parent::__construct(200, $message, $previous, [], 0);
    }

    /**
     * @return string the exception PSR3 {@see LogLevel}
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * Override the default report method.
     * See {@see Handler::report()} for details.
     */
    public function report(): void
    {
        /** @var LoggerInterface $logger */
        $logger = app()->make(LoggerInterface::class);

        $logger->log($this->level, $this->getMessage(), ['exception' => $this]);
    }

    /**
     * Returns true when exception message is safe to be displayed to a client.
     *
     * @return bool If the exception is safe.
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Returns string describing a category of the error.
     *
     * Value "graphql" is reserved for errors produced by query parsing or validation, do not use it.
     *
     * @return string The exception category.
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Default GraphQL exception payload ({@see RendersErrorsExtensions} for details).
     *
     * @return mixed[] The extension payload.
     */
    public function extensionsContent(): array
    {
        return [];
    }
}
