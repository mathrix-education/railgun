<?php

declare(strict_types=1);

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http404NotFoundException;
use Throwable;

class SponsorNotFound extends Http404NotFoundException
{
    public function __construct($email, ?Throwable $previous = null)
    {
        parent::__construct(
            ['email' => $email],
            "The sponsor with the email `$email` cannot be found",
            $previous
        );
    }
}
