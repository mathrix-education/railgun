<?php
namespace App\Controllers;

class TrainingController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /trainings/{?page}/{?perPage} Index
     * @apiGroup Trainings
     * @apiName TrainingsIndex
     * @apiDescription Retrieve trainings
     * @apiPermission moderator
     * @apiUse Pagination
     * @apiUse TrainingArray
     * @apiUse Http401Unauthorized
     */


    /**
     * @apiVersion 1.0.0
     * @api {get} /trainings/{id} Get
     * @apiGroup Trainings
     * @apiName TrainingGet
     * @apiDescription Get an training by id
     * @apiPermission public
     * @apiParam {int} id The training id
     * @apiUse TrainingModel
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 1.0.0
     * @api {post} /trainings Add
     * @apiGroup Trainings
     * @apiName TrainingAdd
     * @apiDescription Add a training
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiUse TrainingRequest
     * @apiUse TrainingModel
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 1.0.0
     * @api {patch} /trainings/{id} Edit
     * @apiGroup Trainings
     * @apiName TrainingEdit
     * @apiDescription Edit an training
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiParam {int} id The training id
     * @apiUse TrainingRequest
     * @apiUse TrainingModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 1.0.0
     * @api {delete} /trainings/{id} Delete
     * @apiGroup Trainings
     * @apiName TrainingDelete
     * @apiDescription Delete an training
     * @apiUse Bearer
     * @apiPermission moderator
     * @apiParam {int} id The training id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */
}
