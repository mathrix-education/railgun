<?php
/**
* @apiDefine NotionRequest
 * @apiParam (Data) {string} name The notion name
 * @apiParam (Data) {string} [slug=slug(name)] The notion slug
 * @apiParam (Data) {bool} visible The notion visibility
 * @apiParam (Data) {int} chapter_id The notion chapter_id
 * @apiParam (Data) {array} [link] The models to link with the notion
 * @apiParam (Data) {int[]} [link.activities] The activities ids to sync with the activity. The given order wll be used to set
 * the order in the many-to-many relationship. If not specified, do not change anything.
 * @apiParam (Data) {int[]} [link.chapters] The chapters ids to sync with the notion. The given order wll be used to set
 * the order in the many-to-many relationship. If not specified, do not change anything.
 */
