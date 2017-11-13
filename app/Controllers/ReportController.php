<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ReportController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix
 */
class ReportController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /reports/{?page}/{?perPage} Index
     * @apiGroup Reports
     * @apiName ReportsIndex
     * @apiDescription Add a report
     * @apiPermission public
     * @apiUse Bearer
     * @apiUse Pagination
     * @apiUse Report
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     *
     * @param Request $request
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     */
    public function index(Request $request, int $page = 1, int $perPage = 10): JsonResponse
    {
        // Check permission
        if ($request->user() !== null && $request->user()->cant("index", $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        return response()->json(
            Report::limit($perPage)
                ->with("module")
                ->offset(($page - 1) * $perPage)
                ->get()
        );
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /reports Add
     * @apiGroup Reports
     * @apiName ReportAdd
     * @apiDescription Add a report
     * @apiPermission public
     * @apiUse Bearer
     * @apiUse ReportData
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */
}