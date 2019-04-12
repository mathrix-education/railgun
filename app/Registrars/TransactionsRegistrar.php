<?php

namespace App\Registrars;

use App\Controllers\TransactionsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class TransactionsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class TransactionsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = TransactionsController::class;

        $this->get("transactions/by-user/{userId:[a-z0-9\_]+}", "$controller@byUser");

        $this->group(["middleware" => "scope:support"], function () use ($controller) {
            $this->get("transactions/by-subscription/{subscriptionId:[a-z0-9\_]+}", "$controller@bySubscription");
            $this->get("transactions/{transactionId:[a-z0-9\_]+}", "$controller@find");
            $this->post("transactions/{transactionId:[a-z0-9\_]+}/void", "$controller@void");
            $this->post("transactions/{transactionId:[a-z0-9\_]+}/refund", "$controller@refund");
        });
    }
}
