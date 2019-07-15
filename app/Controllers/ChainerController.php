<?php

namespace App\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Chainer\Chainer;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;

/**
 * Class ChainerController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ChainerController extends BaseController
{
    /**
     * POST /chain
     * Handle chained requests.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     */
    public function chain(Request $request): JsonResponse
    {
        return (new Chainer($request))->process();
    }
}
