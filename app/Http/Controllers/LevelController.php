<?php
namespace App\Http\Controllers;

/**
 * Class LevelController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class LevelController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /levels/{?page}/{?perPage} Index
     * @apiGroup Levels
     * @apiName LevelsIndex
     * @apiDescription Retrieve levels
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse LevelArray
     */
}