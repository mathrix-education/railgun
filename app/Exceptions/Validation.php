<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Psr\Log\LogLevel;
use Throwable;
use function trans;

/**
 * Wrapper of {@see ValidationException}.
 */
class Validation extends RailgunException
{
    protected string $level    = LogLevel::NOTICE;
    protected string $category = 'validation';

    private Validator $validator;

    public function __construct(
        Validator $validator,
        ?string $message = null,
        ?Throwable $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        if ($message === 'The given data was invalid.') {
            /** Default validation message of the {@see ValidationException} */
            $message = (string)trans('validation.fails');
        }

        parent::__construct($message, $previous, $code, $headers);
        $this->validator = $validator;
    }

    /**
     * Create a new instance based on a {@see ValidationException}
     *
     * @param ValidationException $source The Laravel ValidationException.
     *
     * @return static
     */
    public static function from(ValidationException $source): self
    {
        return new self($source->validator, $source->getMessage(), $source);
    }

    public function extensionsContent(): array
    {
        return $this->validator->errors()->messages();
    }
}
