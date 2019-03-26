<?php

namespace App\Controllers;

use App\Utils\Chainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class ChainerController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
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
     * @throws \Mathrix\Lumen\Exceptions\Http\Http400BadRequestException
     */
    public function chain(Request $request): JsonResponse
    {
        return (new Chainer($request))->process();
    }
}
