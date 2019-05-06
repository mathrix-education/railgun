<?php

namespace App\Registrars;

use App\Controllers\ActivitiesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ActivitiesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ActivitiesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = ActivitiesController::class;

        $this->rest("activities", $controller, [
            "index" => "scope:activities:read",
            "get" => null,
            "post" => "scope:activities:write",
            "patch" => "scope:activities:write",
            "delete" => "scope:activities:delete",
            "related:modules" => null
        ]);

        $this->group(["middleware" => "scope:logged"], function () use ($controller) {
            $this->patch("activities/{activityId:[1-9]\d*}/mark", "$controller@mark");
            $this->patch("activities/{activityId:[1-9]\d*}/unmark", "$controller@unmark");
        });
    }
}
