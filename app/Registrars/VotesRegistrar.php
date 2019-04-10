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

            $this->post("votes/comment/{commentId:[1-9]\d*}", "$controller@vote");
            $this->delete("votes/comment/{commentId:[1-9]\d*}", "$controller@unVote");
        });
    }
}
