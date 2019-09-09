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
    public function register() : void
    {
        $controller = AuthController::class;
        $uuidRegex  = '[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}';

        $this->post('auth/login', "$controller@login");
        $this->post('auth/login/facebook', "$controller@facebook");
        $this->get('auth/public-key', "$controller@publicKey");
        $this->post('auth/forgot', "$controller@forgot");
        $this->post("auth/reset/{token:$uuidRegex}", "$controller@reset");
    }
}
