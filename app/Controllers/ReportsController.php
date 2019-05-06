<?php

namespace App\Controllers;

use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;

/**
 * Class ReportsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class ReportsController extends BaseController
{
    /**
     * POST /report
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function post(Request $request): JsonResponse
    {
        /** @var Report $report */
        $report = new Report($request->all());
        $report->save();

        return new JsonResponse($report->fresh());
    }
}
