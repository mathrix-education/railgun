<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\ActivitiesController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class ActivitiesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register() : void
    {
        $controller = ActivitiesController::class;

        $this->rest([
            'std:index' => 'scope:activities:read',
            'std:get' => null,
            'std:post' => 'scope:activities:write',
            'std:patch' => 'scope:activities:write',
            'std:delete' => 'scope:activities:delete',
            'rel:get:modules' => null,
        ]);

        $this->group(['middleware' => 'logged'], function () use ($controller) : void {
            $this->patch('activities/{activityId}/mark', "$controller@mark");
            $this->patch('activities/{activityId}/unmark', "$controller@unmark");
        });
    }
}
