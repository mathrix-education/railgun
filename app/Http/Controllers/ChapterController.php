<?php
namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Http\Controllers\BaseController;

/**
 * Chapter ChapterController
 *
 * @author Mathrix Team
 * @package App\Http\Controllers
 * @version 1.0.0
 */
class ChapterController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /chapters Index
     * @apiGroup Chapters
     * @apiName ChaptersIndex
     * @apiDescription Retrieve chapters
     * @apiPermission public
     * @apiUse ChapterArray
     * @return JsonResponse
     */
    public function index()
    {
        return $this->http200Success(Chapter::all());
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /chapters/{id} Get
     * @apiGroup Chapters
     * @apiName ChapterGet
     * @apiDescription Get a chapter by id
     * @apiPermission public
     * @apiParam {int} id The chapter id
     * @apiUse Chapter
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $chapter = Chapter::where('id', $id)->firstOrFail();

        return $this->http200Success($chapter);
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /chapters Add
     * @apiGroup Chapters
     * @apiName ChapterAdd
     * @apiDescription Add a chapter
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse ChapterData
     * @apiUse Chapter
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
        if ($request->user()->cant('add')) {
            return $this->http401Unauthorized();
        }

        $chapter = new Chapter();
        $chapter->fill($request->all());

        if (!$chapter->save()) {
            return $chapter->handleResponse($this->http422Save());
        }

        return $this->http200Success($chapter);
    }


    /**
     * @apiVersion 1.0.0
     * @api {patch} /chapters/{id} Edit
     * @apiGroup Chapters
     * @apiName ChapterEdit
     * @apiDescription Edit a chapter
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The chapter id
     * @apiUse ChapterData
     * @apiUse Chapter
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
        /** @var Chapter $chapter */
        $chapter = Chapter::where('id', $id)->firstOrFail();

        if ($request->user()->cant('edit', Chapter::class)) {
            return $this->http401Unauthorized();
        }

        $chapter->fill($request->all());

        if (!$chapter->save()) {
            return $chapter->handleResponse($this->http422Save());
        }

        return $this->http200Success($chapter);
    }


    /**
     * @apiVersion 1.0.0
     * @api {delete} /chapters/{id} Delete (NYI)
     * @apiGroup Chapters
     * @apiName ChapterDelete
     * @apiDescription Delete a chapter
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The chapter id
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function delete(int $id)
    {
        return $this->http501NotImplemented();
    }
}
