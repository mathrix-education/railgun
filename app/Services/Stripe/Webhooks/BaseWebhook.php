<?php

declare(strict_types=1);

namespace App\Services\Stripe\Webhooks;

use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

abstract class BaseWebhook
{
    /**
     * Handle the webhook.
     *
     * @param mixed $object The Stripe object handled by the webhook.
     *
     * @return SuccessJsonResponse
     */
    abstract public function handle($object): SuccessJsonResponse;
}
