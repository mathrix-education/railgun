<?php

namespace App\Exceptions;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Throwable;

/**
 * Class InvalidSponsorException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class InvalidSponsorException extends Http400BadRequestException
{
    protected const ERROR = "invalid_sponsor";


    public function __construct(User $sponsor, Throwable $previous = null)
    {
        parent::__construct(
            [
                "email" => $sponsor->email,
            ],
            "User email `{$sponsor->email}` is not a valid sponsor",
            $previous
        );
    }
}
