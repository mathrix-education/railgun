<?php

namespace App\Registrars;

use App\Controllers\VotesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class VotesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class VotesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->group(["middleware" => "scope:registered"], function () {
            $controller = VotesController::class;

            $this->post("votes/{votable_type:comment}/{votable_id:[1-9]\d*}/up", "$controller@up");
            $this->post("votes/{votable_type:comment}/{votable_id:[1-9]\d*}/down", "$controller@down");
        });
    }
}
