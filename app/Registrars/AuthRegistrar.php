<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\AuthController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class AuthRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = AuthController::class;

        $this->post('auth/login', "$controller@login");
        $this->post('auth/login/facebook', "$controller@facebook");
        $this->get('auth/public-key', "$controller@publicKey");
        $this->post('auth/forgot', "$controller@forgot");
        $this->post('auth/reset', "$controller@reset");
    }
}
