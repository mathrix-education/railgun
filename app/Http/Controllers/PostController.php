<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Http\Controllers\BaseController;

/**
 * Class PostController
 *
 * @author Mathrix Team
 * @package App\Http\Controllers
 * @version 1.0.0
 */
class PostController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /posts Index
     * @apiGroup Posts
     * @apiName PostsIndex
     * @apiDescription Retrieve posts
     * @apiPermission public
     * @apiParam {int} [page=1] The page number
     * @apiParam {int} [perPage=10] The post count per page
     * @apiUse PostArray
     *
     * @param Request $request
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     */
    public function index(Request $request, int $page = 1, int $perPage = 10)
    {
        if ($page < 1 || $perPage < 1) {
            return $this->http400InvalidRequest('page and perPage have to be strictly positive');
        }

        return $this->http200Success(
            Post::limit($perPage)
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
     * @apiUse Post
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $post = Post::where('id', $id)->firstOrFail();

        return $this->http200Success($post);
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /posts Add
     * @apiGroup Posts
     * @apiName PostAdd
     * @apiDescription Add a post
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse PostData
     * @apiUse Post
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
        if ($request->user()->cant('add', Post::class)) {
            return $this->http401Unauthorized();
        }

        $post = new Post();
        $post->fill($request->all());

        if (!$post->save()) {
            return $post->handleResponse($this->http422Save());
        }

        return $this->http200Success($post);
    }


    /**
     * @apiVersion 1.0.0
     * @api {patch} /posts/{id} Edit
     * @apiGroup Posts
     * @apiName PostEdit
     * @apiDescription Edit a post
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiParam {int} id The post id
     * @apiUse PostData
     * @apiUse Post
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     * @apiUse 422Save
     * @apiUse 422Validation
     *
     * @param Request $request
     * @param int $id, factory(Post::class)->raw()
     *
     * @return JsonResponse
     */
    public function edit(Request $request, int $id)
    {
        /** @var Post $post */
        $post = Post::where('id', $id)->firstOrFail();

        if ($request->user()->cant('edit', $post)) {
            return $this->http401Unauthorized();
        }

        $post->fill($request->all());

        if (!$post->save()) {
            return $post->handleResponse($this->http422Save());
        }

        return $this->http200Success($post);
    }


    /**
     * @apiVersion 1.0.0
     * @api {delete} /posts/{id} Delete
     * @apiGroup Posts
     * @apiName PostDelete
     * @apiDescription Delete a post
     * @apiUse Bearer
     * @apiPermission moderator
     * @apiParam {int} id The post id
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
        /** @var Post $post */
        $post = Post::where('id', $id)->firstOrFail();

        if ($request->user()->cant('delete', $post)) {
            return $this->http401Unauthorized();
        }

        if (!$post->delete()) {
            return $post->handleResponse($this->http422Delete());
        }

        return $this->http200Success();
    }
}
