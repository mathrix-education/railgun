<?php

namespace App\Http\Controllers;

use App\Models\Notion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class NotionController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class NotionController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /notions Index
     * @apiGroup Notions
     * @apiName NotionsIndex
     * @apiDescription Retrieve notions
     * @apiPermission moderator
     * @apiUse NotionArray
     * @return JsonResponse
     * @apiUse Http400InvalidRequest_Pagination
     * @apiUse Http401Unauthorized
     */


    /**
     * @apiVersion 0.8.1
     * @api {get} /notions/{id} Get
     * @apiGroup Notions
     * @apiName NotionGet
     * @apiDescription Get a notion by id
     * @apiPermission public
     * @apiParam {int} id The notion id
     * @apiUse Notion
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 0.8.1
     * @api {post} /notions Add
     * @apiGroup Notions
     * @apiName NotionAdd
     * @apiDescription Add a notion
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiUse NotionData
     * @apiUse Notion
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 0.8.1
     * @api {patch} /notions/{id} Edit
     * @apiGroup Notions
     * @apiName NotionEdit
     * @apiDescription Edit a notion
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiParam {int} id The notion id
     * @apiUse NotionData
     * @apiUse Notion
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 0.8.1
     * @api {delete} /notions/{id} Delete
     * @apiGroup Notions
     * @apiName NotionDelete
     * @apiDescription Delete a notion
     * @apiUse Bearer
     * @apiPermission moderator
     * @apiParam {int} id The notion id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */
}
