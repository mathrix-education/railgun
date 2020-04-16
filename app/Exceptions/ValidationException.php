<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Psr\Log\LogLevel;
use Throwable;
use function trans;

/**
 * Wrapper of {@see IlluminateValidationException}.
 */
class ValidationException extends HttpException
{
    protected string  $level    = LogLevel::NOTICE;
    protected string  $category = 'validation';
    /** @var string[] $errors The validation errors */
    private array $errors;

    /**
     * @param mixed[]        $errors   The validation errors.
     * @param string|null    $message  The validation failure message.
     * @param Throwable|null $previous The previous exception, if any.
     */
    public function __construct(
        ?string $message = null,
        array $errors = [],
        ?Throwable $previous = null
    )
    {
        if ($message === 'The given data was invalid.') {
            /** Default validation message of the {@see ValidationException}, override it by our own. */
            $message = (string)trans('validation.fails');
        }

        parent::__construct($message, $previous);

        $this->errors = $errors;
    }

    /**
     * @return mixed[] The validation error messages.
     */
    public function extensions(): array
    {
        return ['validation' => $this->errors];
    }
}
