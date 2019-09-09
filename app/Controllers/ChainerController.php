<?php

declare(strict_types=1);

namespace App\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Chainer\Chainer;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;

class ChainerController extends BaseController
{
    /**
     * POST /chain
     * Handle chained requests.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @throws Http400BadRequestException
     */
    public function chain(Request $request) : JsonResponse
    {
        return (new Chainer($request))->process();
    }
}
