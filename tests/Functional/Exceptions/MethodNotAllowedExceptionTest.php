<?php

declare(strict_types=1);

namespace Tests\Functional\Exceptions;

use Railgun\Exceptions\MethodNotAllowedException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException as SymfonyMethodNotAllowedHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException as SymfonyMethodNotAllowedException;
use Throwable;

class MethodNotAllowedExceptionTest extends ExceptionTestCase
{
    /**
     * @throws Throwable
     */
    public function testSymfonyMethodNotAllowedHttpException(): void
    {
        $this->expectException(MethodNotAllowedException::class);

        $this->transform(static function (): void {
            throw new SymfonyMethodNotAllowedHttpException(['post']);
        });
    }

    /**
     * @throws Throwable
     */
    public function testSymfonyMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedException::class);

        $this->transform(static function (): void {
            throw new SymfonyMethodNotAllowedException(['post']);
        });
    }
}
