<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CategoryController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class CategoryController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /categories Index
     * @apiGroup Categories
     * @apiName CategoriesIndex
     * @apiDescription Retrieve categories
     * @apiPermission public
     * @apiUse CategoryArray
     * @return JsonResponse
     */
    public function index()
    {
        return $this->http200Success(Category::all());
    }


    /**
     * @apiVersion 0.8.1
     * @api {get} /categories/{id} Get
     * @apiGroup Categories
     * @apiName CategoryGet
     * @apiDescription Get a category by id
     * @apiPermission public
     * @apiParam {int} id The category id
     * @apiUse Category
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $category = Category::where('id', $id)->firstOrFail();

        return $this->http200Success($category);
    }


    /**
     * @apiVersion 0.8.1
     * @api {post} /categories Add
     * @apiGroup Categories
     * @apiName CategoryAdd
     * @apiDescription Add a category
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse CategoryData
     * @apiUse Category
     * @apiUse 401Unauthorized
     * @apiUse 422Validation
     * @apiUse 422Save
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        if ($request->user()->cant('add', Category::class)) {
            return $this->http401Unauthorized();
        }

        $category = new Category($request->all());

        if (!$category->save()) {
            return $category->handleResponse($this->http422Save());
        }

        return $this->http200Success($category);
    }


    /**
     * @apiVersion 0.8.1
     * @api {patch} /categories/{id} Edit
     * @apiGroup Categories
     * @apiName CategoryEdit
     * @apiDescription Edit a category
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The category id
     * @apiUse CategoryData
     * @apiUse Category
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     * @apiUse 422Save
     * @apiUse 422Validation
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function edit(Request $request, int $id)
    {
        /** @var Category $category */
        $category = Category::where('id', $id)->firstOrFail();

        if ($request->user()->cant('edit', Category::class)) {
            return $this->http401Unauthorized();
        }

        $category->fill($request->all());

        if (!$category->save()) {
            return $category->handleResponse($this->http422Save());
        }

        return $this->http200Success($category);
    }


    /**
     * @apiVersion 0.8.1
     * @api {delete} /categories/{id} Delete (Not implemented)
     * @apiGroup Categories
     * @apiName CategoryDelete
     * @apiDescription Delete a category
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The category id
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function delete(Request $request, int $id)
    {
        return $this->http501NotImplemented();
    }
}
