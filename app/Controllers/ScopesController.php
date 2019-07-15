<?php

namespace App\Controllers;

use Mathrix\Lumen\JWT\Auth\ScopesLoader;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

/**
 * Class ScopesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.1.0
 */
class ScopesController extends BaseController
{
    public function all(): SuccessJsonResponse
    {
        return new SuccessJsonResponse(ScopesLoader::getRegisteredScopes());
    }
}
