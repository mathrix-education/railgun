<?php

namespace App\Http\Controllers;

use App\Models\Notion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Http\Controllers\BaseController;

/**
 * Class NotionController
 *
 * @author Mathrix Team
 * @package App\Http\Controllers
 * @version 1.0.0
 */
class NotionController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /notions Index
     * @apiGroup Notions
     * @apiName NotionsIndex
     * @apiDescription Retrieve notions
     * @apiPermission public
     * @apiUse NotionArray
     * @return JsonResponse
     */
    public function index()
    {
        return $this->http200Success(Notion::all());
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /notions/{id} Get
     * @apiGroup Notions
     * @apiName NotionGet
     * @apiDescription Get a notion by id
     * @apiPermission public
     * @apiParam {int} id The notion id
     * @apiUse Notion
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $notion = Notion::where('id', $id)->firstOrFail();

        return $this->http200Success($notion);
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /notions Add
     * @apiGroup Notions
     * @apiName NotionAdd
     * @apiDescription Add a notion
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse NotionData
     * @apiUse Notion
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
        if ($request->user()->cant('add', Notion::class)) {
            return $this->http401Unauthorized();
        }

        $notion = new Notion();
        $notion->fill($request->all());

        if (!$notion->save()) {
            return $notion->handleResponse($this->http422Save());
        }

        return $this->http200Success($notion);
    }


    /**
     * @apiVersion 1.0.0
     * @api {patch} /notions/{id} Edit
     * @apiGroup Notions
     * @apiName NotionEdit
     * @apiDescription Edit a notion
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The notion id
     * @apiUse NotionData
     * @apiUse Notion
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
        /** @var Notion $notion */
        $notion = Notion::where('id', $id)->firstOrFail();

        if ($request->user()->cant('edit', $notion)) {
            return $this->http401Unauthorized();
        }

        $notion->fill($request->all());

        if (!$notion->save()) {
            return $notion->handleResponse($this->http422Save());
        }

        return $this->http200Success($notion);
    }


    /**
     * @apiVersion 1.0.0
     * @api {delete} /notions/{id} Delete
     * @apiGroup Notions
     * @apiName NotionDelete
     * @apiDescription Delete a notion
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The notion id
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function delete(Request $request, int $id)
    {
        /** @var Notion $notion */
        $notion = Notion::where('id', '=', $id)->firstOrFail();

        if ($request->user()->cant('delete', $notion)) {
            return $this->http401Unauthorized();
        }

        if (!$notion->delete()) {
            return $notion->handleResponse($this->http422Delete());
        }

        return $this->http200Success();
    }
}
