<?php

namespace App\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\JWT\Auth\ScopesLoader;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Class ScopesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.1.0
 */
class ScopesController extends BaseController
{
    public function all()
    {
        $scopes = ScopesLoader::getRegisteredScopes();

        return new JsonResponse($scopes);
    }
}
