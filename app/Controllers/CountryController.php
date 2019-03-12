<?php

namespace App\Controllers;

use App\Exceptions\Http501NotImplementedException;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CountryController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class CountryController extends BaseController
{
    /**
     * Delete a country; not yet implemented.
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
        $user->canOrFail("delete", Country::class);

        throw new Http501NotImplementedException();
    }
}
