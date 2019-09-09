<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\PlansController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class PlansRegistrar extends BaseRegistrar
{
    public function register() : void
    {
        $controller = PlansController::class;
        $this->get('/plans', "$controller@all");
    }
}
