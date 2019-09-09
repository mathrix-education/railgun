<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\TransactionsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class TransactionsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register() : void
    {
        $controller = TransactionsController::class;

        $this->get('transactions/by-user/{userId:[a-z0-9\_]+}', "$controller@byUser");

        $this->group(['middleware' => 'scope:transactions:read'], function () use ($controller) : void {
            $this->get('transactions/by-subscription/{subscriptionId:[a-z0-9\_]+}', "$controller@bySubscription");
            $this->get('transactions/{transactionId:[a-z0-9\_]+}', "$controller@find");
        });

        $this->group(['middleware' => 'scope:transactions:write'], function () use ($controller) : void {
            $this->post('transactions/{transactionId:[a-z0-9\_]+}/void', "$controller@void");
            $this->post('transactions/{transactionId:[a-z0-9\_]+}/refund', "$controller@refund");
        });
    }
}
