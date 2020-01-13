<?php

declare(strict_types=1);

namespace App\Controllers;

use Mathrix\Lumen\JWT\Auth\ScopesLoader;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\DataResponse;

/**
 * Serve routes starting with /scopes.
 */
class ScopesController extends BaseController
{
    /**
     * GET /scopes
     * Get the scopes list.
     *
     * @return DataResponse The scopes list.
     */
    public function all(): DataResponse
    {
        return new DataResponse(ScopesLoader::getRegisteredScopes());
    }
}
