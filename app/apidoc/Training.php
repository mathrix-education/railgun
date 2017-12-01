<?php
/**
 * @apiDefine TrainingData
 * @apiParam (Data) {string} name The training name
 * @apiParam (Data) {string} slug The training slug
 * @apiParam (Data) {string} slug The training description
 */
/**
 * @apiDefine TrainingArray
 * @apiSuccess {Training[]} trainings Array of countries
 * @apiSuccess {int} trainings.id The training id
 * @apiSuccess {string} trainings.name The training name
 * @apiSuccess {string} trainings.slug The training slug
 * @apiSuccess {string} trainings.slug The training description
 */
/**
 * @apiDefine Training
 * @apiSuccess {int} id The training id
 * @apiSuccess {string} name The training name
 * @apiSuccess {string} slug The training slug
 * @apiSuccess {string} slug The training description
 */