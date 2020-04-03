<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Railgun\Exceptions\HttpException;
use Stripe\Event;
use Throwable;
use function trans;

/**
 * Thrown when a Stripe {@see Event} is not supported by the webhook handler.
 */
class UnsupportedEventException extends HttpException
{
    protected string $category = 'billing';

    public function __construct(Event $event, ?Throwable $previous = null)
    {
        parent::__construct(trans('billing.errors.unsupported_event', ['eventType' => $event->type]), $previous);

        $this->setStatusCode(400);
        $this->setData(['event' => $event->jsonSerialize()]);
    }
}
