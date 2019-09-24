<?php

declare(strict_types=1);

namespace App\Controllers;

use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

/**
 *
 */
class InvoicesController extends BaseController
{
    public function standardPost(Request $request): SuccessJsonResponse
    {
    }

    public function pay(Request $request): SuccessJsonResponse
    {

    }
}
