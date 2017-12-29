<?php
/**
 * @apiDefine CommentRequest
 * @apiParam (Data) {string-null} [content=null] The comment content
 * @apiParam (Data) {int} [score=0] The comment score
 * @apiParam (Data) {int-null} [parent_id=null] The comment parent_id
 * @apiParam (Data) {int} [user_id] The comment user_id
 * @apiParam (Data) {int} commentable_id The comment commentable_id
 * @apiParam (Data) {string=module,post} commentable_type The comment commentable_type
 * @apiParam (Data) {string} [created_at] The comment created_at
 * @apiParam (Data) {string} [updated_at] The comment updated_at
 * @apiParam (Data) {string-null} [deleted_at=null] The comment deleted_at
 */
