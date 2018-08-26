<?php
/**
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since ${CARET}
 * @copyright Mathrix SA
 */


namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Utils\Chainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ChainController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 4.1.0
 * @copyright Mathrix SA
 */
class ChainController extends BaseController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     */
    public function chain(Request $request)
    {
        $chainer = new Chainer($request);

        $response = $chainer->process();

        return $response;
    }
}
