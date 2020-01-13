<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\CommentsController;
use Mathrix\Lumen\Zero\Exceptions\InvalidArgument;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class CommentsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     *
     * @throws InvalidArgument
     */
    public function register(): void
    {
        $controller = CommentsController::class;

        $this->get('comments/latest', [
            'uses' => "$controller@latest",
            'middleware' => 'scope:comments:read',
        ]);

        $this->registerCRUDRoutes([
            'list' => 'scope:comments:read',
            'read' => 'scope:comments:read',
            'create' => 'logged',
            'update' => 'logged',
            'delete' => 'scope:comments:delete',
        ]);
    }
}
