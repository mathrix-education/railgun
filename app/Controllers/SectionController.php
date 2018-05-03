<?php
namespace App\Controllers;

/**
 * Class SectionController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class SectionController extends BaseController
{
    /**
     * @apiVersion 4.0.0
     * @api {get} /sections/{?page}/{?perPage} Index
     * @apiGroup Sections
     * @apiName SectionsIndex
     * @apiDescription Retrieve sections
     * @apiPermission moderator
     * @apiUse Pagination
     * @apiUse SectionArray
     * @apiUse Http401Unauthorized
     */


    /**
     * @apiVersion 4.0.0
     * @api {get} /sections/{id} Get
     * @apiGroup Sections
     * @apiName SectionGet
     * @apiDescription Get an section by id
     * @apiPermission public
     * @apiParam {int} id The section id
     * @apiUse SectionModel
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 4.0.0
     * @api {post} /sections Add
     * @apiGroup Sections
     * @apiName SectionAdd
     * @apiDescription Add a section
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiUse SectionRequest
     * @apiUse SectionModel
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 4.0.0
     * @api {patch} /sections/{id} Edit
     * @apiGroup Sections
     * @apiName SectionEdit
     * @apiDescription Edit an section
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiParam {int} id The section id
     * @apiUse SectionRequest
     * @apiUse SectionModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 4.0.0
     * @api {delete} /sections/{id} Delete
     * @apiGroup Sections
     * @apiName SectionDelete
     * @apiDescription Delete an section
     * @apiUse Bearer
     * @apiPermission moderator
     * @apiParam {int} id The section id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */
}
