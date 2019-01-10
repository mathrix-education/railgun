<?php
namespace App\Controllers;

use App\Exceptions\Http401UnauthorizedException;
use App\Models\Class_;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class DisciplineController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class DisciplineController extends BaseController
{
    /**
     * Get visible disciplines by class id
     *
     * @param Request $request
     * @param int     $classId
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function byClass(Request $request, int $classId)
    {
        $class = Class_::findOrFail($classId);

        if ($request->user() !== null && $request->user()
                ->cant("byClass", $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        $response = new JsonResponse($class->disciplines);

        return $response;
    }
}
