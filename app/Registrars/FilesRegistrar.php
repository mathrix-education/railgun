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

        $this->group(["middleware" => "scope:files:read", "prefix" => "files"], function () use ($controller) {
            $this->get("explore[/{directory:[a-zA-Z0-9\_\-\/\.]+}]", "$controller@explore");
            $this->get("{file:[a-zA-Z0-9\_\-\/\.]+}", "$controller@info");
        });

        $this->group(["middleware" => "scope:files:write", "prefix" => "files"], function () use ($controller) {
            $this->post("{file:[a-zA-Z0-9\_\-\/\.]+}", "$controller@upload");
            $this->patch("{file:[a-zA-Z0-9\_\-\/\.]+}", "$controller@rename");
        });

        $this->delete("files/{file:[a-zA-Z0-9\_\-\/\.]+}", [
            "uses" => "$controller@remove",
            "middleware" => "scope:files:delete"
        ]);

    }
}
