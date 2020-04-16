<?php

declare(strict_types=1);

namespace Tests\Functional\Exceptions;

use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;
use Railgun\Exceptions\ValidationException;
use Throwable;

/**
 * @testdox Validation Exception
 */
class ValidationExceptionTest extends ExceptionTestCase
{
    /**
     * @testdox convert exceptions from Illuminate Validator.
     *
     * @throws Throwable
     */
    public function testIlluminateException(): void
    {
        /** @var Validator $validator */
        $validator = ValidatorFacade::make([], ['foo' => 'required']);

        $this->expectException(ValidationException::class);

        $this->transform(static fn() => $validator->validate());
    }
}
