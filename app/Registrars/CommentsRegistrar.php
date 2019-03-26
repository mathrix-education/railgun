<?php

namespace App\Registrars;

use App\Controllers\CommentsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class CommentsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class CommentsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = CommentsController::class;

        $this->rest("comments", $controller, [
            "index" => "moderator",
            "get" => "moderator",
            "post" => "registered",
            "patch" => "registered",
            "delete" => "moderator"
        ]);

        $this->get("comments/latest[/{page:[0-9]+}/{perPage:[1-9]\d*}]", "$controller@latest");

        $this->get("by-{what:module|comment}/{id:[1-9]\d*}[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]",
            "$controller@by");
    }
}
