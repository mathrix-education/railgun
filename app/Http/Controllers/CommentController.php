<?php
namespace App\Http\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\BaseModel;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Module;
use App\Models\Post;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CommentController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class CommentController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /comments Index
     * @apiGroup Comments
     * @apiName CommentsIndex
     * @apiDescription Retrieve comments
     * @apiPermission moderator
     * @apiUse CommentArray
     * @apiUse Http401Unauthorized
     */


    /**
     * @apiVersion 0.8.1
     * @api {get} /comments/{id} Get
     * @apiGroup Comments
     * @apiName CommentGet
     * @apiDescription Get a class by id
     * @apiPermission public
     * @apiParam {int} id The class id
     * @apiUse Comment
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 0.8.1
     * @api {post} /comments Add
     * @apiGroup Comments
     * @apiName CommentAdd
     * @apiDescription Add class
     * @apiPermission registered
     * @apiUse Bearer
     * @apiUse CommentData
     * @apiUse Comment
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 0.8.1
     * @api {patch} /comments/{id} Edit
     * @apiGroup Comments
     * @apiName CommentEdit
     * @apiDescription Edit a class
     * @apiPermission registered for own comment, moderator otherwise
     * @apiUse Bearer
     * @apiParam {int} id The comment id
     * @apiUse CommentData
     * @apiUse Comment
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 0.8.1
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
     * @apiVersion 0.8.1
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

        throw_if($commentable_class === null, Http400InvalidRequestException::class);

        // Ensure that the morphed exists
        /** @var Module|Post|Course $commentable */
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
}
