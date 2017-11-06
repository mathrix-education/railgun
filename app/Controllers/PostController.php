<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PostController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class PostController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /posts/{?page}/{?perPage} Index
     * @apiGroup Posts
     * @apiName PostsIndex
     * @apiDescription Retrieve posts
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse PostArray
     *
     * @param Request $request
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     */
    public function index(Request $request, int $page = 1, int $perPage = 10): JsonResponse
    {
        return response()->json(
            Post::limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->orderByDesc('created_at')
                ->get()
        );
    }


    /**
     * @apiVersion 0.8.1
     * @api {get} /posts/{id} Get
     * @apiGroup Posts
     * @apiName PostGet
     * @apiDescription Get a post by id
     * @apiPermission public
     * @apiParam {int} id The post id
     * @apiUse Post
     * @apiUse Http404ModelNotFound
     */

    /**
     * @apiVersion 0.8.1
     * @api {post} /posts Add
     * @apiGroup Posts
     * @apiName PostAdd
     * @apiDescription Add a post
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse PostData
     * @apiUse Post
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 0.8.1
     * @api {patch} /posts/{id} Edit
     * @apiGroup Posts
     * @apiName PostEdit
     * @apiDescription Edit a post
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The post id
     * @apiUse PostData
     * @apiUse Post
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 0.8.1
     * @api {delete} /posts/{id} Delete
     * @apiGroup Posts
     * @apiName PostDelete
     * @apiDescription Delete a post
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The post id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */
}
