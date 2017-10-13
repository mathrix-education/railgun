<?php
/**
 * @apiDefine CategoryData
 * @apiParam (Data) {string} name The category name
 * @apiParam (Data) {string} [slug=slug(name)] The category slug
 */
/**
 * @apiDefine CategoryArray
 * @apiSuccess {Category[]} categories Array of categories
 * @apiSuccess {int} categories.id The category id
 * @apiSuccess {string} categories.name The category name
 * @apiSuccess {string} categories.slug The category slug
 */
/**
 * @apiDefine Category
 * @apiSuccess {int} id The category id
 * @apiSuccess {string} name The category name
 * @apiSuccess {string} slug The category slug
 */