<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JsonSerializable;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;
use Throwable;
use Traversable;
use function app;
use function is_array;
use function is_int;
use function is_string;
use function iterator_to_array;

/**
 * Base exception for GraphQL errors.
 */
abstract class HttpException extends SymfonyHttpException implements RendersErrorsExtensions, Responsable
{
    /** @var int $statusCode The response status code. */
    protected int $statusCode = 200;
    /** @var string|mixed[] $data The response data. */
    protected $data = [];
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
     * @return int The response status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $newStatusCode The new status code.
     */
    public function setStatusCode(int $newStatusCode): void
    {
        $this->statusCode = $newStatusCode;
    }

    /**
     * @param string|int|mixed[]|stdClass|Arrayable|JsonSerializable|Traversable $data The response data.
     */
    public function setData($data): void
    {
        if (is_string($data) || is_int($data)) {
            $this->data = (string)$data;
        } elseif (is_array($data) || $data instanceof stdClass) {
            $this->data = (array)$data;
        } elseif ($data instanceof Arrayable) {
            $this->data = $data->toArray();
        } elseif ($data instanceof JsonSerializable) {
            $this->data = $data->jsonSerialize();
        } elseif ($data instanceof Traversable) {
            $this->data = iterator_to_array($data);
        } else {
            throw new RuntimeException('Unsupported data type');
        }
    }

    /**
     * @return mixed[]|string The response data.
     */
    public function getData()
    {
        $data = $this->data ?? $this->extensionsContent();

        if (is_array($data)) {
            $data['message'] = $this->getMessage();
        }

        return $data;
    }

    /**
     * @param Request $request The Illuminate HTTP request.
     *
     * @return Response The response associated with the exception.
     *
     * phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function toResponse($request): Response
    {
        if (is_string($this->data)) {
            $response = new Response($this->getData(), $this->getStatusCode(), $this->getHeaders());
        } else {
            $response = new JsonResponse($this->getData(), $this->getStatusCode(), $this->getHeaders());
        }

        return $response;
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
