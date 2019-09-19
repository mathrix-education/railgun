<?php

declare(strict_types=1);

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;

class UnknownBraintree extends Http400BadRequestException
{
    protected $message = 'Unknown Braintree exception';
}
