<?php

namespace App\Registrars;

use App\Controllers\ChaptersController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ChaptersRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ChaptersRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = ChaptersController::class;
        $this->rest("chapters", $controller, [
            "index" => "scope:chapters:read",
            "get" => null,
            "post" => "scope:chapters:write",
            "patch" => "scope:chapters:write",
            "delete" => "scope:chapters:delete",
            "related:notions" => null
        ]);

        $this->get("chapters/{chapterId:[1-9]\d*}/recursive", "$controller@recursive");
    }
}
