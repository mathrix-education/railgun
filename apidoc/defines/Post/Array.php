<?php
/**
 * @apiDefine PostArray
 * @apiSuccess {Post[]} posts Array of posts
 * @apiSuccess {string} posts.title The post title
 * @apiSuccess {string} posts.slug The post title
 * @apiSuccess {string} posts.content The post content
 * @apiSuccess {int} posts.module_id The post module id
 * @apiSuccess {int} posts.category_id The post category id
 * @apiSuccess {int} posts.user_id The post user id
 * @apiSuccess {int} posts.module The post module
 * @apiSuccess {int} posts.module.id The post module id
 * @apiSuccess {string=course} posts.module.type The post module type
 * @apiSuccess {string} posts.module.content The post module content
 * @apiSuccess {bool} posts.module.visible The post module visibility
 * @apiSuccess {datetime} posts.created_at The post created date
 * @apiSuccess {datetime} posts.updated_at The post updated date
 */
