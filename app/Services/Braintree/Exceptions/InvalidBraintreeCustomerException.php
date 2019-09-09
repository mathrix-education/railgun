<?php

declare(strict_types=1);

namespace App\Services\Braintree\Exceptions;

use App\Models\User;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;
use function trans;

class InvalidBraintreeCustomerException extends Http400BadRequestException
{
    public function __construct(User $user, ?Throwable $previous = null)
    {
        parent::__construct(
            ['id' => $user->id],
            trans('braintree.customer.invalid', ['id' => $user->id]),
            $previous
        );
    }
}
