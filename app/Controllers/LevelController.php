<?php
namespace App\Controllers;

/**
 * Class LevelController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix SA
 */
class LevelController extends BaseController
{
    /**
     * @apiVersion 4.0.0
     * @api {get} /levels/{?page}/{?perPage} Index
     * @apiGroup Levels
     * @apiName LevelsIndex
     * @apiDescription Retrieve levels
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse LevelArray
     */
}
