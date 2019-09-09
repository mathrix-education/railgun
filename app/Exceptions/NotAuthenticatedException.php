<?php

declare(strict_types=1);

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http401UnauthorizedException;

class NotAuthenticatedException extends Http401UnauthorizedException
{
}
