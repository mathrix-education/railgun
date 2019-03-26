<?php

namespace App\Registrars;

use App\Controllers\ContentController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ContentRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ContentRegistrar extends BaseRegistrar
{
    public function register(): void
    {
        $this->get("content/search/{query}[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]",
            ContentController::class . "@searchGlobal");
    }
}
