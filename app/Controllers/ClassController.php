<?php

namespace App\Controllers;

use App\Exceptions\Http501NotImplementedException;
use App\Models\Class_;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ClassController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class ClassController extends BaseController
{
    /**
     * Delete a class; not yet implemented.
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http501NotImplementedException
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->canOrFail("delete", Class_::class);

        throw new Http501NotImplementedException();
    }
}
