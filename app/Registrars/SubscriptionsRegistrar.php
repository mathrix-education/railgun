<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\SubscriptionsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class SubscriptionsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = SubscriptionsController::class;

        $this->group(['middleware' => 'logged'], function () use ($controller): void {
            $this->post('subscriptions', "$controller@standardPost");
            $this->get('subscriptions/{subscriptionId}/invoices', "$controller@invoices");
            $this->patch('subscriptions/{subscriptionId}/card', "$controller@card");
        });
    }
}
