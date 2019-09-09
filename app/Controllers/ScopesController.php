<?php

declare(strict_types=1);

namespace App\Controllers;

use Mathrix\Lumen\JWT\Auth\ScopesLoader;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

class ScopesController extends BaseController
{
    public function all() : SuccessJsonResponse
    {
        return new SuccessJsonResponse(ScopesLoader::getRegisteredScopes());
    }
}
