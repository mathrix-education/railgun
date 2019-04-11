<?php

namespace App\Controllers;

use App\Models\Discipline;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class DisciplinesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class DisciplinesController extends BaseController
{
    /**
     * GET /disciplines/slug/{disciplineSlug}
     * Get a discipline by its slug.
     *
     * @param string $disciplineSlug The discipline slug.
     *
     * @return JsonResponse
     */
    public function getBySlug(string $disciplineSlug)
    {
        $discipline = Discipline::query()
            ->where("slug", "=", $disciplineSlug)
            ->firstOrFail();

        return new JsonResponse($discipline);
    }
}
