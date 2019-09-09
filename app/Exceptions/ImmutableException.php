<?php

declare(strict_types=1);

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;

class ImmutableException extends Http400BadRequestException
{
}
