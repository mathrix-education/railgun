<?php

declare(strict_types=1);

namespace Railgun\Billing\Webhooks;

use Illuminate\Routing\Controller;
use Railgun\Exceptions\InvalidAuthToken;

class StripeWebhooksController extends Controller
{
    public function handle(): void
    {
        throw new InvalidAuthToken();
    }
}
