<?php
/**
 * @author Mathrix Back-End Team
 *
 * @since ${CARET}
 *
 * @copyright Mathrix SA
 */

namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Utils\Chainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ChainController.
 *
 * @author Mathrix Back-End Team
 *
 * @since 4.1.0
 *
 * @copyright Mathrix SA
 */
class ChainController extends BaseController
{
    /**
     * @param Request $request
     *
     * @throws Http400InvalidRequestException
     *
     * @return JsonResponse
     */
    public function chain(Request $request)
    {
        $chainer = new Chainer($request);

        $response = $chainer->process();

        return $response;
    }
}
