<?php

declare(strict_types=1);

namespace Railgun\Billing\Webhooks;

use Illuminate\Routing\Controller;
use Railgun\Exceptions\InvalidAuthTokenException;

class StripeWebhooksController extends Controller
{
    public function handle(): void
    {
        throw new InvalidAuthTokenException();
    }
}
