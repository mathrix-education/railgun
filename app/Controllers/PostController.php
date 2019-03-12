<?php

namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Post;
use App\Responses\PaginationJsonResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PostController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class PostController extends BaseController
{
    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /posts/{?page}/{?perPage} Index
     * @apiGroup       Posts
     * @apiName        PostsIndex
     * @apiDescription Retrieve posts
     * @apiPermission  public
     * @apiUse         Pagination
     * @apiUse         PostArray
     *
     * @param Request $request The request
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     */
    public function index(Request $request, int $page = 1, int $perPage = 10): JsonResponse
    {
        $query = Post::with("module")
            ->orderBy("created_at", "desc");

        return new PaginationJsonResponse($query, $page, $perPage);
    }

    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /posts/{id} Get
     * @apiGroup       Posts
     * @apiName        PostGet
     * @apiDescription Get a post by id
     * @apiPermission  public
     * @apiParam {int} id The post id
     * @apiUse         PostModel
     * @apiUse         Http404ModelNotFound
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {post} /posts Add
     * @apiGroup       Posts
     * @apiName        PostAdd
     * @apiDescription Add a post
     * @apiPermission  admin
     * @apiUse         Bearer
     * @apiUse         PostRequest
     * @apiUse         PostModel
     * @apiUse         Http401Unauthorized
     * @apiUse         Http422ModelValidation
     * @apiUse         Http422ModelSave
     *
     * @param Request $request The request
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     *
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        if ($request->user() !== null && $request->user()
                ->cant("add", Post::class)) {
            throw new Http401UnauthorizedException();
        }

        /** @var Post $post */
        $post = new Post($request->all());
        $post->user_id = $this->getUserByRequestOrUnauthorized($request)->id;
        $post->save();

        return response()->json($post);
    }

    /*
     * @apiVersion 4.0.0
     * @api {patch} /posts/{id} Edit
     * @apiGroup Posts
     * @apiName PostEdit
     * @apiDescription Edit a post
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The post id
     * @apiUse PostRequest
     * @apiUse PostModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */

    /*
     * @apiVersion 4.0.0
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
