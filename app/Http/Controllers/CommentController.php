<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Http\Controllers\BaseController;
use Mathrix\Lumen\Models\BaseModel;

/**
 * Comment CommentController
 *
 * @author Mathrix Team
 * @package App\Http\Controllers
 * @version 1.0.0
 */
class CommentController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /comments Index
     * @apiGroup Comments
     * @apiName CommentsIndex
     * @apiDescription Retrieve comments
     * @apiPermission public
     * @apiUse CommentArray
     *
     * @return JsonResponse
     */
    public function index()
    {
        return $this->http200Success(Comment::all());
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /comments/{commentable_type}/{commentable_id}/{?page}/{?perPage} Type
     * @apiGroup Comments
     * @apiName CommentsIndex
     * @apiDescription Retrieve comments by type
     * @apiPermission public
     * @apiParam {string=post,course,module} commentable_type the commentable_type
     * @apiParam {int} commentable_id the commentable id
     * @apiParam {int} [page=1] the page to fetch
     * @apiParam {int} [perPage=10] the comments per page to fetch (excluding children)
     * @apiUse CommentArray
     *
     * @param string $commentable_type
     * @param int|string $commentable_id
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     */
    public function type(string $commentable_type, int $commentable_id, int $page = 1, int $perPage = 10)
    {
        // Ensure that the commentable model is morphed
        /** @var BaseModel $commentable_class */
        $commentable_class = Relation::getMorphedModel($commentable_type);

        if ($commentable_class == null) {
            return $this->http400InvalidRequest();
        }

        if ($page < 1 || $perPage < 1) {
            return $this->http400InvalidRequest('page and perPage have to be strictly positive');
        }


        // Ensure that the morphed exists
        $commentable = $commentable_class::where('id', '=', $commentable_id)->firstOrFail();

        // Retrieve comments
        return $this->http200Success(
            $commentable->comments()
                ->with(['user', 'children'])
                ->limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get()
        );
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /comments/{id} Get
     * @apiGroup Comments
     * @apiName CommentGet
     * @apiDescription Get a class by id
     * @apiPermission public
     * @apiParam {int} id The class id
     * @apiUse Comment
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $class = Comment::where('id', $id)
            ->with('user')
            ->firstOrFail();

        return $this->http200Success($class);
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /comments Add
     * @apiGroup Comments
     * @apiName CommentAdd
     * @apiDescription Add class
     * @apiPermission student
     * @apiUse Bearer
     * @apiUse CommentData
     * @apiUse Comment
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
        $class = new Comment();
        $class->fill($request->all());

        if (!$class->save()) {
            return $class->handleResponse($this->http422Save());
        }

        return $this->http200Success($class);
    }


    /**
     * @apiVersion 1.0.0
     * @api {patch} /comments/{id} Edit
     * @apiGroup Comments
     * @apiName CommentEdit
     * @apiDescription Edit a class
     * @apiPermission student for own comment, moderator for others
     * @apiUse Bearer
     * @apiParam {int} id The comment id
     * @apiUse CommentData
     * @apiUse Comment
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
        /** @var Comment $comment */
        $comment = Comment::where('id', $id)->firstOrFail();

        if ($request->user()->cant('edit', $comment)) {
            return $this->http401Unauthorized();
        }

        $comment->fill($request->all());

        if (!$comment->save()) {
            return $comment->handleResponse($this->http422Save());
        }

        return $this->http200Success($comment);
    }


    /**
     * @apiVersion 1.0.0
     * @api {delete} /comments/{id} Delete
     * @apiGroup Comments
     * @apiName CommentDelete
     * @apiDescription Delete a comment and its children
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiParam {int} id The comment id
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
        /** @var Comment $comment */
        $comment = Comment::where('id', '=', $id)->firstOrFail();

        if ($request->user()->cant('delete', $comment)) {
            return $this->http401Unauthorized();
        }

        if (!$comment->delete()) {
            return $comment->handleResponse($this->http422Delete());
        }

        return $this->http200Success();
    }
}
