<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\PostsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class PostsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register() : void
    {
        $controller = PostsController::class;
        $this->get('posts/latest', "$controller@latest");

        $this->rest([
            'std:index' => null,
            'std:get' => 'scope:posts:read',
            'std:post' => 'scope:posts:write',
            'std:patch' => 'scope:posts:write',
            'std:delete' => 'scope:posts:delete',
        ]);
    }
}
