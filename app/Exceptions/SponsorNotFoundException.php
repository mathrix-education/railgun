<?php

namespace App\Exceptions;

use Mathrix\Lumen\Exceptions\Http\Http404NotFoundException;
use Throwable;

/**
 * Class SponsorNotFoundException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class SponsorNotFoundException extends Http404NotFoundException
{
    protected const ERROR = "sponsor_not_found";


    public function __construct($email, Throwable $previous = null)
    {
        parent::__construct(
            ["email" => $email],
            "The sponsor with the email `$email` cannot be found",
            $previous
        );
    }
}
