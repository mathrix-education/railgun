<?php

namespace App\Registrars;

use App\Controllers\SearchController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class ContentRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class SearchRegistrar extends BaseRegistrar
{
    public function register(): void
    {
        $this->get("search/{gradeId}/{query}",
            SearchController::class . "@content");
    }
}
