<?php
/**
 * @apiDefine LessonArray
 * @apiSuccess {Lesson[]} lessons Array of lessons
 * @apiSuccess {int} lessons.id The lesson id
 * @apiSuccess {string} lessons.title The lesson title
 * @apiSuccess {string} lessons.slug The lesson slug
 * @apiSuccess {string} lessons.image The lesson image
 * @apiSuccess {int} lessons.section_id The lesson section_id
 * @apiSuccess {int} lessons.module.id The lesson module_id
 * @apiSuccess {int} lessons.module The lesson module
 * @apiSuccess {int} lessons.module.id The lesson module id
 * @apiSuccess {string=course} lessons.module.type The lesson module type
 * @apiSuccess {string} lessons.module.content The lesson module content
 * @apiSuccess {bool} lessons.module.visible The lesson module visibility
 * @apiSuccess {string} lessons.created_at The lesson created_at
 * @apiSuccess {string} lessons.updated_at The lesson updated_at
 */
