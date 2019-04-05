<?php

namespace App\Registrars;

use App\Controllers\FilesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class FilesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class FilesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->group(["middleware" => "scope:moderator", "prefix" => "files"], function () {
            $controller = FilesController::class;

            $this->post("{path:[a-zA-Z0-9\_\-\/\.]+}", "$controller@upload");
            $this->get("explore/{directory:[a-zA-Z0-9\_\-\/\.]+}", "$controller@explore");
            $this->get("{path:[a-zA-Z0-9\_\-\/\.]+}", "$controller@info");
            $this->patch("{path:[a-zA-Z0-9\_\-\/\.]+}", "$controller@rename");
            $this->delete("{path:[a-zA-Z0-9\_\-\/\.]+}", "$controller@remove");
        });

    }
}
