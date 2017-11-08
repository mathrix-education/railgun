<?php
namespace App\Controllers;

use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http501NotImplementedException;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CategoryController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix
 */
class CategoryController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /categories/{?page}/{?perPage} Index
     * @apiGroup Categories
     * @apiName CategoriesIndex
     * @apiDescription Retrieve categories
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse CategoryArray
     */


    /**
     * @apiVersion 1.0.0
     * @api {get} /categories/{id} Get
     * @apiGroup Categories
     * @apiName CategoryGet
     * @apiDescription Get a category by id
     * @apiPermission public
     * @apiParam {int} id The category id
     * @apiUse Category
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 1.0.0
     * @api {post} /categories Add
     * @apiGroup Categories
     * @apiName CategoryAdd
     * @apiDescription Add a category
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse CategoryData
     * @apiUse Category
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 1.0.0
     * @api {patch} /categories/{id} Edit
     * @apiGroup Categories
     * @apiName CategoryEdit
     * @apiDescription Edit a category
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The category id
     * @apiUse CategoryData
     * @apiUse Category
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 1.0.0
     * @api {delete} /categories/{id} Delete (Not implemented)
     * @apiGroup Categories
     * @apiName CategoryDelete
     * @apiDescription Delete a category
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The category id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http501NotImplementedException
     * @throws \Exception
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        throw new Http501NotImplementedException();

        return parent::delete($request, $id);
    }
}
