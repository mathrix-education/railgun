<?php

namespace Railgun\Billing\Exceptions;

use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class InvalidCart extends BadRequestHttpException implements RendersErrorsExtensions
{
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'cart';
    }

    public function extensionsContent(): array
    {
        return [];
    }
}
