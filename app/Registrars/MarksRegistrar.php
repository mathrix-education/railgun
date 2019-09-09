<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\MarksController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class MarksRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register() : void
    {
        $controller = MarksController::class;

        $this->get('marks/activities/to-revise', [
            'uses' => "$controller@activitiesToRevise",
            'middleware' => 'logged',
        ]);
    }
}
