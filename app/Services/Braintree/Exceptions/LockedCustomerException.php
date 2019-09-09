<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\User;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

class LockedCustomerException extends Http400BadRequestException
{
    public function __construct(User $subscription, ?Throwable $previous = null)
    {
        parent::__construct(['id' => $subscription->id], "User {$subscription->id} is locked", $previous);
    }
}
