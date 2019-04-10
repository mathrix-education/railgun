<?php

namespace App\Registrars;

use App\Controllers\SearchController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ContentRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class SearchRegistrar extends BaseRegistrar
{
    public function register(): void
    {
        $this->get("search/{gradeId:[1-9]\d*}/{query:[^\/]+}/{page:\d*}/{perPage:[1-9]\d*}",
            SearchController::class . "@content");
    }
}
