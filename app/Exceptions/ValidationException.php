<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException as IlluminateValidationException;
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
    private Validator $validator;

    /**
     * @param Validator      $validator The validator instance which owns the failed validation rules.
     * @param string|null    $message   The validation failure message.
     * @param Throwable|null $previous  The previous exception, if any.
     */
    public function __construct(
        Validator $validator,
        ?string $message = null,
        ?Throwable $previous = null
    )
    {
        if ($message === 'The given data was invalid.') {
            /** Default validation message of the {@see ValidationException}, override it by our own. */
            $message = (string)trans('validation.fails');
        }

        parent::__construct($message, $previous);
        $this->validator = $validator;
    }

    /**
     * Create a new instance based on a {@see ValidationException}
     *
     * @param IlluminateValidationException $source The Laravel ValidationException.
     *
     * @return static
     */
    public static function from(IlluminateValidationException $source): self
    {
        return new self($source->validator, $source->getMessage(), $source);
    }

    /**
     * @return mixed[] The validation error messages.
     */
    public function extensionsContent(): array
    {
        return $this->validator->errors()->messages();
    }
}
