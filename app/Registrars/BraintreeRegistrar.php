<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\BraintreeController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class BraintreeRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register() : void
    {
        $controller = BraintreeController::class;

        $this->post('braintree/webhook', "$controller@webhook");

        $this->get('braintree/client-token', [
            'uses' => "$controller@clientToken",
            'middleware' => 'logged',
        ]);
    }
}
