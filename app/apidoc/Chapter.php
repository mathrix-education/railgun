<?php
/**
 * @apiDefine ChapterData
 * @apiParam (Data) {string} name The chapter name
 * @apiParam (Data) {string} [slug=slug(name)] The chapter slug
 * @apiParam (Data) {int} class_id The chapter class id
 * @apiParam (Data) {int} discipline_id The chapter discipline id
 * @apiParam (Data) {bool} chapters.visible The chapter visibility
 * @apiParam (Data) {int[]} [link.notions] The notions ids to sync with the chapter. The given order wll be used to set
 * the order in the many-to-many relationship. If not specified, do not change anything.
 */
/**
 * @apiDefine ChapterArray
 * @apiSuccess {Chapter[]} chapters Array of chapters
 * @apiSuccess {int} chapters.id The chapter id
 * @apiSuccess {string} chapters.name The chapter name
 * @apiSuccess {string} chapters.slug The chapter slug
 * @apiSuccess  {int} chapters.class_id The chapter class id
 * @apiSuccess {int} chapters.discipline_id The chapter discipline id
 * @apiSuccess {bool} chapters.visible The chapter visibility
 */
/**
 * @apiDefine Chapter
 * @apiSuccess {int} id The chapter id
 * @apiSuccess {string} name The chapter name
 * @apiSuccess {string} slug The chapter slug
 * @apiSuccess {int} class_id The chapter class id
 * @apiSuccess {int} discipline_id The chapter discipline id
 * @apiSuccess {bool} visible The chapter visibility
 */