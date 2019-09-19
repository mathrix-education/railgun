<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\FilesController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class FilesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = FilesController::class;

        $this->group(['middleware' => 'scope:files:read'], function () use ($controller): void {
            $this->get('/files/list[/{directory:[a-zA-Z0-9\_\-\/\.]+}]', "$controller@list");
            $this->get('/files/{file:[a-zA-Z0-9\_\-\/\.]+}', "$controller@get");
        });

        $this->group(['middleware' => 'scope:files:write'], function () use ($controller): void {
            $this->post('/files/{file:[a-zA-Z0-9\_\-\/\.]+}', "$controller@post");
            $this->patch('/files/{file:[a-zA-Z0-9\_\-\/\.]+}', "$controller@patch");
        });

        $this->delete('/files/{file:[a-zA-Z0-9\_\-\/\.]+}', [
            'uses' => "$controller@delete",
            'middleware' => 'scope:files:delete',
        ]);
    }
}
