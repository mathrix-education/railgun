<?php
/**
 * @apiDefine LessonModel
 * @apiSuccess {int} id The lesson id
 * @apiSuccess {string} title The lesson title
 * @apiSuccess {string} slug The lesson slug
 * @apiSuccess {string} image The lesson image
 * @apiSuccess {int} order The lesson order
 * @apiSuccess {int} section_id The lesson section_id
 * @apiSuccess {int} module_id The lesson module_id
 * @apiSuccess {int} module The lesson module
 * @apiSuccess {int} module.id The lesson module id
 * @apiSuccess {string=course} module.type The lesson module type
 * @apiSuccess {string} module.content The lesson module content
 * @apiSuccess {bool} module.visible The lesson module visibility
 * @apiSuccess {string} created_at The lesson created_at
 * @apiSuccess {string} updated_at The lesson updated_at
 *
 */
