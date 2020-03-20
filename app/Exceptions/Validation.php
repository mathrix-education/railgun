<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;
use function trans;

/**
 * Alternative of {@see ValidationException} which implements {@see RendersErrorsExtensions}.
 */
class Validation extends UnprocessableEntityHttpException implements RendersErrorsExtensions
{
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

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'validation';
    }

    public function extensionsContent(): array
    {
        return $this->validator->errors()->messages();
    }
}
