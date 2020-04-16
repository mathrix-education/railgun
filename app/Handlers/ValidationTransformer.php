<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Closure;
use Illuminate\Validation\ValidationException as IlluminateValidationException;
use Nuwave\Lighthouse\Exceptions\GenericException;
use Railgun\Exceptions\ValidationException;
use Throwable;
use function preg_match;
use function trans;

/**
 * Convert the validation exceptions.
 *
 * @see GenericException
 * @see IlluminateValidationException
 */
class ValidationTransformer implements TransformsExceptions
{
    /**
     * Transform a exception into another.
     *
     * @param Throwable $original The original exception.
     * @param Closure   $next     The next exception transformer.
     *
     * @return Throwable The transformed exception.
     */
    public function transform(Throwable $original, Closure $next): Throwable
    {
        if ($original instanceof GenericException && $original->getCategory() === 'validation') {
            // Convert validation thrown from lighthouse
            $result = preg_match(
                '/Validation failed for the field \[(.*)]\./',
                $original->getMessage(),
                $matches,
            );

            if ($result) {
                $original->message = trans('validation.fails_field', ['field' => $matches[1]]);

                return $next(new ValidationException(
                    $original->getMessage(),
                    $original->getExtensions()['validation'],
                ));
            }
        }

        if ($original instanceof IlluminateValidationException) {
            // Convert validation exceptions
            return $next(new ValidationException(
                $original->getMessage(),
                $original->validator->errors()->messages(),
            ));
        }

        return $next($original);
    }
}
