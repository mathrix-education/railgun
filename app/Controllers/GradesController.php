<?php

namespace App\Controllers;

use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class GradesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class GradesController extends BaseController
{

    /**
     * GET /grades/slug/{gradeSlug}
     *
     * @param string $gradeSlug The grade slug.
     *
     * @return JsonResponse
     */
    public function getBySlug(string $gradeSlug)
    {
        $grade = Grade::query()
            ->where("slug", "=", $gradeSlug)
            ->firstOrFail();

        return new JsonResponse($grade);
    }
}
