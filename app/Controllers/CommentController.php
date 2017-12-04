<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\BaseModel;
use App\Models\Comment;
use App\Models\Module;
use App\Models\Post;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CommentController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix SA
 */
class CommentController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /comments/{?page}/{?perPage} Index
     * @apiGroup Comments
     * @apiName CommentsIndex
     * @apiDescription Retrieve comments
     * @apiPermission moderator
     * @apiUse Pagination
     * @apiUse CommentArray
     * @apiUse Http401Unauthorized
     */


    /**
     * @apiVersion 1.0.0
     * @api {get} /comments/{id} Get
     * @apiGroup Comments
     * @apiName CommentGet
     * @apiDescription Get a class by id
     * @apiPermission public
     * @apiParam {int} id The class id
     * @apiUse CommentModel
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 1.0.0
     * @api {post} /comments Add
     * @apiGroup Comments
     * @apiName CommentAdd
     * @apiDescription Add class
     * @apiPermission registered
     * @apiUse Bearer
     * @apiUse CommentRequest
     * @apiUse CommentModel
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
        if ($request->user() !== null && $request->user()->cant("add", $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        /** @var BaseModel $model */
        $comment = new Comment($request->all());
        $comment->user_id = $this->getUserByRequestOrUnauthorized($request)->id;
        $comment->save();

        return response()->json($comment);
    }


    /**
     * @apiVersion 1.0.0
     * @api {patch} /comments/{id} Edit
     * @apiGroup Comments
     * @apiName CommentEdit
     * @apiDescription Edit a class
     * @apiPermission registered for own comment, moderator otherwise
     * @apiUse Bearer
     * @apiParam {int} id The comment id
     * @apiUse CommentRequest
     * @apiUse CommentModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 1.0.0
     * @api {delete} /comments/{id} Delete
     * @apiGroup Comments
     * @apiName CommentDelete
     * @apiDescription Delete a comment and its children
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiParam {int} id The comment id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */


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
     * @param int $commentable_id
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     */
    public function type(string $commentable_type, int $commentable_id, int $page = 1, int $perPage = 10)
    {
        // Ensure that the commentable model is morphed
        /** @var BaseModel $commentable_class */
        $commentable_class = Relation::getMorphedModel($commentable_type);

        if ($commentable_class === null) {
            throw new Http400InvalidRequestException();
        }

        // Ensure that the morphed exists
        /** @var Module|Post $commentable */
        $commentable = $commentable_class::where("id", "=", $commentable_id)->firstOrFail();

        // Retrieve comments
        return response()->json(
            $commentable->comments()
                ->with(["user", "children", "children.user"])
                ->limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get()
        );
    }


    /**
     * @apiVersion 1.0.1
     * @api {post} /comments/search Search
     * @apiGroup Comments
     * @apiName CommentSearch
     * @apiDescription Search for comments
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiParam (Data) {string} [field1] The comment field1 search value (use % as wildcard)
     * @apiParam (Data) {string} [field2] The comment field2 search value (use % as wildcard)
     * @apiParam (Data) {array} order The order data
     * @apiParam (Data) {string} order.column The order column
     * @apiParam (Data) {string=asc,desc} order.dir The order direction
     */
}
