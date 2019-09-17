<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\ModulesController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class ModulesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            'std:index' => 'scope:modules:read',
            'std:get' => null,
            'std:post' => 'scope:modules:write',
            'std:patch' => 'scope:modules:write',
            'std:delete' => 'scope:modules:delete',
            'rel:get:reports' => 'scope:reports:read',
        ]);

        $controller = ModulesController::class;

        $this->get('/modules/{moduleId:[1-9]\d*}/comments', "$controller@getComments");
        $this->get('/modules/{moduleId:[1-9]\d*}/chapter/recursive', "$controller@chapterRecursive");

        $this->patch('/modules/{moduleId:[1-9]\d*}/mark', [
            'uses' => "$controller@mark",
            'middleware' => 'logged',
        ]);

        $this->get('modules/courses/{page}/{perPage}', "$controller@courses");
    }
}
