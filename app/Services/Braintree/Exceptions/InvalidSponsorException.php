<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\User;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

class InvalidSponsorException extends Http400BadRequestException
{
    public function __construct(User $sponsor, ?Throwable $previous = null)
    {
        parent::__construct(
            [
                'email' => $sponsor->email,
            ],
            "User email `{$sponsor->email}` is not a valid sponsor",
            $previous
        );
    }
}
