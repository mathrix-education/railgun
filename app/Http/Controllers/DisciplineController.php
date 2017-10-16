<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class DisciplineController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class DisciplineController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /disciplines Index
     * @apiGroup Disciplines
     * @apiName DisciplinesIndex
     * @apiDescription Retrieve disciplines
     * @apiPermission public
     * @apiUse DisciplineArray
     */


    /**
     * @apiVersion 0.8.1
     * @api {get} /disciplines/{id} Get
     * @apiGroup Disciplines
     * @apiName DisciplineGet
     * @apiDescription Get a discipline by id
     * @apiPermission public
     * @apiParam {int} id The discipline id
     * @apiUse Discipline
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $discipline = Discipline::where('id', $id)->firstOrFail();

        return $this->http200Success($discipline);
    }


    /**
     * @apiVersion 0.8.1
     * @api {post} /disciplines Add
     * @apiGroup Disciplines
     * @apiName DisciplineAdd
     * @apiDescription Add a discipline
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse DisciplineData
     * @apiUse Discipline
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
        if ($request->user()->cant('add', Discipline::class)) {
            return $this->http401Unauthorized();
        }

        $discipline = new Discipline();
        $discipline->fill($request->all());

        if (!$discipline->save()) {
            return $discipline->handleResponse($this->http422Save());
        }

        return $this->http200Success($discipline);
    }


    /**
     * @apiVersion 0.8.1
     * @api {patch} /disciplines/{id} Edit
     * @apiGroup Disciplines
     * @apiName DisciplineEdit
     * @apiDescription Edit a discipline
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The discipline id
     * @apiUse DisciplineData
     * @apiUse Discipline
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
        /** @var Discipline $discipline */
        $discipline = Discipline::where('id', $id)->firstOrFail();

        if ($request->user()->cant('edit', Discipline::class)) {
            return $this->http401Unauthorized();
        }

        $discipline->fill($request->all());

        if (!$discipline->save()) {
            return $discipline->handleResponse($this->http422Save());
        }

        return $this->http200Success($discipline);
    }


    /**
     * @apiVersion 0.8.1
     * @api {delete} /disciplines/{id} Delete (Not implemented)
     * @apiGroup Disciplines
     * @apiName DisciplineDelete
     * @apiDescription Delete a discipline
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The discipline id
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
