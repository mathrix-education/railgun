<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Services\Braintree\Models\Subscription;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;
use function implode;

class InvalidSubscriptionStatusException extends Http400BadRequestException
{
    public function __construct(Subscription $subscription, array $allowedStatuses, ?Throwable $previous = null)
    {
        $message = "Subscription {$subscription->id} status is invalid";

        if (! empty($allowedStatuses)) {
            $message .= ' (allowed statuses: [`' . implode('`, `', $allowedStatuses) . '`])';
        }

        parent::__construct([
            'id' => $subscription->id,
            'status' => $subscription->status,
            'allowed_statuses' => $allowedStatuses,
        ], $message, $previous);
    }
}
