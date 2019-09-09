<?php

declare(strict_types=1);

namespace App\Services\Stripe\Webhooks;

use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\Event;

abstract class BaseWebhook
{
    abstract public function handle(Event $event) : SuccessJsonResponse;
}
