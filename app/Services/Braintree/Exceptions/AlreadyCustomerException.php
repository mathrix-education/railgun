<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\User;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

class AlreadyCustomerException extends Http400BadRequestException
{
    public function __construct(User $subscription, ?Throwable $previous = null)
    {
        parent::__construct(['id' => $subscription->id], "User {$subscription->id} already a customer", $previous);
    }
}
