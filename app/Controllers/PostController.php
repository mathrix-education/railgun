<?php
namespace App\Controllers;


use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PostController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix SA
 */
class PostController extends BaseController
{
    /**
     * @apiVersion 1.0.0
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
            Post::with("module")
                ->limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->orderByDesc('created_at')
                ->get()
        );
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /posts/{id} Get
     * @apiGroup Posts
     * @apiName PostGet
     * @apiDescription Get a post by id
     * @apiPermission public
     * @apiParam {int} id The post id
     * @apiUse PostModel
     * @apiUse Http404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        $model = Post::with("module")->where("id", $id)->firstOrFail();

        return response()->json($model);
    }

    /**
     * @apiVersion 1.0.0
     * @api {post} /posts Add
     * @apiGroup Posts
     * @apiName PostAdd
     * @apiDescription Add a post
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse PostRequest
     * @apiUse PostModel
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function add(Request $request): JsonResponse
    {
        if ($request->user() !== null && $request->user()->cant("add", Post::class)) {
            throw new Http401UnauthorizedException();
        }

        /** @var Post $post */
        $post = new Post($request->all());
        $post->user_id = $this->getUserByRequestOrUnauthorized($request)->id;
        $post->save();

        return response()->json($post);
    }


    /**
     * @apiVersion 1.0.0
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


    /**
     * @apiVersion 1.0.0
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
