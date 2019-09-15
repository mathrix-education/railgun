<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\CommentsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class CommentsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = CommentsController::class;

        $this->get('comments/latest', [
            'uses' => "$controller@latest",
            'middleware' => 'scope:comments:read',
        ]);

        $this->rest([
            'std:index' => 'scope:comments:read',
            'std:get' => 'scope:comments:read',
            'std:post' => 'logged',
            'std:patch' => 'logged',
            'std:delete' => 'scope:comments:delete',
        ]);
    }
}
