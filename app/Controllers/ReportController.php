<?php

namespace App\Controllers;

/**
 * Class ReportController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class ReportController extends BaseController
{
    /*
     * @apiVersion 4.0.0
     * @api {get} /reports/{?page}/{?perPage} Index
     * @apiGroup Reports
     * @apiName ReportsIndex
     * @apiDescription Add a report
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiUse Pagination
     * @apiUse ReportModel
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */

    /*
     * @apiVersion 4.0.0
     * @api {get} /reports/{id} Get
     * @apiGroup Reports
     * @apiName ReportGet
     * @apiDescription Get a report by id
     * @apiPermission moderator
     * @apiParam {int} id The report id
     * @apiUse ReportModel
     * @apiUse CommentArray
     * @apiUse Http404ModelNotFound
     */

    /*
     * @apiVersion 4.0.0
     * @api {post} /reports Add
     * @apiGroup Reports
     * @apiName ReportAdd
     * @apiDescription Add a report
     * @apiPermission public
     * @apiUse Bearer
     * @apiUse ReportRequest
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */

    /*
     * @apiVersion 4.0.0
     * @api {patch} /reports Edit
     * @apiGroup Reports
     * @apiName ReportEdit
     * @apiDescription Edit a report
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiUse ReportRequest
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */

    /*
     * @apiVersion 4.0.0
     * @api {delete} /reports Delete
     * @apiGroup Reports
     * @apiName ReportDelete
     * @apiDescription Delete a report
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiUse ReportRequest
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */
}
