<?php
/**
 * @apiDefine CommentArray
 * @apiSuccess {Comment[]} comments Array of comments
 * @apiSuccess {int} comments.id The comment id
 * @apiSuccess {string} comments.content The comment content
 * @apiSuccess {int} comments.score The comment score
 * @apiSuccess {int} comments.parent_id The comment parent_id
 * @apiSuccess {int} comments.user_id The comment user_id
 * @apiSuccess {int} comments.commentable_id The comment commentable_id
 * @apiSuccess {string=notion,post} comments.commentable_type The comment commentable_type
 * @apiSuccess {string} comments.created_at The comment created_at
 * @apiSuccess {string} comments.updated_at The comment updated_at
 * @apiSuccess {string} comments.deleted_at The comment deleted_at
 * @apiSuccess {User} comments.user The user
 * @apiSuccess {Comment[]} comments.children The comment children
 */
