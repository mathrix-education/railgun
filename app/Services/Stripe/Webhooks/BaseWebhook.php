<?php

namespace App\Services\Stripe\Webhooks;

use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\Event;

/**
 * Class BaseWebhook.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
abstract class BaseWebhook
{
    public abstract function handle(Event $event): SuccessJsonResponse;
}
