<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\SearchController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class SearchRegistrar extends BaseRegistrar
{
    public function register() : void
    {
        $this->get(
            'search/{gradeId}/{query}',
            SearchController::class . '@content'
        );
    }
}
