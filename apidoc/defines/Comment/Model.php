<?php
/**
 * @apiDefine CommentModel
 * @apiSuccess {int} id The comment id
 * @apiSuccess {string} content The comment content
 * @apiSuccess {int} score The comment score
 * @apiSuccess {int} parent_id The comment parent_id
 * @apiSuccess {int} user_id The comment user_id
 * @apiSuccess {int} commentable_id The comment commentable_id
 * @apiSuccess {string=module,post} commentable_type The comment commentable_type
 * @apiSuccess {string} created_at The comment created_at
 * @apiSuccess {string} updated_at The comment updated_at
 * @apiSuccess {string} deleted_at The comment deleted_at
 * @apiSuccess {User} user The user
 * @apiSuccess {Comment[]} children The comment children
 */
