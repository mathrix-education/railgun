<?php

namespace App\Registrars;

use App\Controllers\FilesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class FilesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class FilesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = FilesController::class;

        $this->group(["middleware" => "scope:moderator", "prefix" => "files"], function () use ($controller) {
            $this->get("explore[/{directory:[a-zA-Z0-9\_\-\/\.]+}]", "$controller@explore");

            $this->post("{file:[a-zA-Z0-9\_\-\/\.]+}", "$controller@upload");
            $this->get("{file:[a-zA-Z0-9\_\-\/\.]+}", "$controller@info");
            $this->patch("{file:[a-zA-Z0-9\_\-\/\.]+}", "$controller@rename");
            $this->delete("{file:[a-zA-Z0-9\_\-\/\.]+}", "$controller@remove");
        });

    }
}
