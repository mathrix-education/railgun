<?php
namespace App\Controllers;

use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http501NotImplementedException;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CategoryController
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class CategoryController extends BaseController
{
    /**
     * @apiVersion 4.0.0
     * @api {get} /categories/{?page}/{?perPage} Index
     * @apiGroup Categories
     * @apiName CategoriesIndex
     * @apiDescription Retrieve categories
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse CategoryArray
     */


    /**
     * @apiVersion 4.0.0
     * @api {get} /categories/{id} Get
     * @apiGroup Categories
     * @apiName CategoryGet
     * @apiDescription Get a category by id
     * @apiPermission public
     * @apiParam {int} id The category id
     * @apiUse CategoryModel
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 4.0.0
     * @api {post} /categories Add
     * @apiGroup Categories
     * @apiName CategoryAdd
     * @apiDescription Add a category
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse CategoryRequest
     * @apiUse CategoryModel
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 4.0.0
     * @api {patch} /categories/{id} Edit
     * @apiGroup Categories
     * @apiName CategoryEdit
     * @apiDescription Edit a category
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The category id
     * @apiUse CategoryRequest
     * @apiUse CategoryModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 4.0.0
     * @api {delete} /categories/{id} Delete (Not implemented)
     * @apiGroup Categories
     * @apiName CategoryDelete
     * @apiDescription Delete a category
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The category id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */
}
