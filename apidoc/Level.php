<?php
/**
 * @apiDefine LevelData
 * @apiParam (Data) {int} level The level number
 * @apiParam (Data) {int} objective The level objective
 * @apiParam (Data) {string} youtube_id The level youtube id
 */
/**
 * @apiDefine LevelArray
 * @apiSuccess {Levels[]} levels Array of levels
 * @apiSuccess {int} levels.id The level id
 * @apiSuccess {int} levels.level The level number
 * @apiSuccess {int} levels.objective The level objective
 * @apiSuccess {string} levels.youtube_id The level youtube id
 */
/**
 * @apiDefine Level
 * @apiSuccess {int} id The level id
 * @apiSuccess {int} level The level number
 * @apiSuccess {int} objective The level objective
 * @apiSuccess {string} youtube_id The level youtube id
 */