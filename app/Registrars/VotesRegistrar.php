<?php

namespace App\Registrars;

use App\Controllers\VotesController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class VotesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class VotesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->group(["middleware" => "logged"], function () {
            $controller = VotesController::class;

            $this->post("votes/comment/{commentId}", "$controller@vote");
            $this->delete("votes/comment/{commentId}", "$controller@unVote");
        });
    }
}
