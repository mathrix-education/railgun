<?php
/**
 * @apiDefine NotionData
 * @apiParam (Data) {string} name The notion name
 * @apiParam (Data) {string} [slug=slug(name)] The notion slug
 * @apiParam (Data) {bool} visible The notion visibility
 * @apiParam (Data) {int} chapter_id The notion chapter_id
 * @apiParam (Data) {array} [link] The models to link with the notion
 * @apiParam (Data) {int[]} [link.activities] The activities ids to sync with the activity. The given order wll be used to set
 * the order in the many-to-many relationship. If not specified, do not change anything.
 */
/**
 * @apiDefine NotionArray
 * @apiSuccess {Notion[]} notions Array of notions
 * @apiSuccess {int} notions.id The notion id
 * @apiSuccess {string} notions.name The notion name
 * @apiSuccess {string} notions.slug The notion slug
 * @apiSuccess {bool} notions.visible The notion visibility
 * @apiSuccess {int} notions.chapter_id The notion chapter_id
 */
/**
 * @apiDefine Notion
 * @apiSuccess {int} id The notion id
 * @apiSuccess {string} name The notion name
 * @apiSuccess {string} slug The notion slug
 * @apiSuccess {bool} visible The notion visibility
 * @apiSuccess {int} chapter_id The notion chapter_id
 */