<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Utils\Chainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ChainController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class ChainController extends BaseController
{
    /**
     * Create a chainer request.
     *
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
