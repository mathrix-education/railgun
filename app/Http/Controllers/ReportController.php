<?php
namespace App\Http\Controllers;

/**
 * Class ReportController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class ReportController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {post} /reports Add
     * @apiGroup Reports
     * @apiName ReportAdd
     * @apiDescription Add a report
     * @apiPermission public
     * @apiUse Bearer
     * @apiUse Report
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */
}