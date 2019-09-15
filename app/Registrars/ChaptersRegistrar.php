<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\ChaptersController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class ChaptersRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            'std:index' => 'scope:chapters:read',
            'std:get' => null,
            'std:post' => 'scope:chapters:write',
            'std:patch' => 'scope:chapters:write',
            'std:delete' => 'scope:chapters:delete',
            'rel:get:notions' => null,
        ]);

        $controller = ChaptersController::class;

        $this->get('chapters/{chapterId}/recursive', "$controller@recursive");
    }
}
