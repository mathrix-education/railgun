<?php

declare(strict_types=1);

namespace App\Controllers;

use Mathrix\Lumen\JWT\Auth\ScopesLoader;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

/**
 * Serve routes starting with /scopes.
 */
class ScopesController extends BaseController
{
    /**
     * GET /scopes
     * Get the scopes list.
     *
     * @return SuccessJsonResponse The scopes list.
     */
    public function all(): SuccessJsonResponse
    {
        return new SuccessJsonResponse(ScopesLoader::getRegisteredScopes());
    }
}
