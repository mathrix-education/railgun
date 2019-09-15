<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\VotesController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class VotesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->group(['middleware' => 'logged'], function (): void {
            $controller = VotesController::class;

            $this->post('votes/comment/{commentId}', "$controller@vote");
            $this->delete('votes/comment/{commentId}', "$controller@unVote");
        });
    }
}
