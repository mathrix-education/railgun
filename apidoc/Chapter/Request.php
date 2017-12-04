<?php
/**
 * @apiDefine ChapterRequest
 * @apiParam (Data) {string} name The chapter name
 * @apiParam (Data) {string} [slug] The chapter slug
 * @apiParam (Data) {int} [order=0] The chapter order
 * @apiParam (Data) {bool} [visible=1] The chapter visible
 * @apiParam (Data) {int} class_id The chapter class_id
 * @apiParam (Data) {int} discipline_id The chapter discipline_id
 * @apiParam (Data) {array} [link] The models to link with the chapter.
 * @apiParam (Data) {int[]} [link.notions] The notions ids to sync with the chapter. The given order wll be used to set
 * the order in the many-to-many relationship. If not specified, do not change anything.
 */
