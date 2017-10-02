/**
 * @apiDefine CommentData
 * @apiParam (Data) {string} content The comment content
 * @apiParam (Data) {int} [parent_id=null] The comment parent id (null for level 0)
 * @apiParam (Data) {int} [user_id] The comment user_id (if not specified, use the current user id)
 * @apiParam (Data) {int} commentable_id The commented model id
 * @apiParam (Data) {string=module,post,course} commentable_type The commented model type
 * @apiParam (Data) {datetime} [created_at] The created date (moderator access)
 * @apiParam (Data) {datetime} [updated_at] The updated date (moderator access)
 * @apiParam (Data) {datetime} [deleted_at] The deleted date (moderator access)
 */
/**
 * @apiDefine CommentArray
 * @apiSuccess {Comment[]} comments Array of countries
 * @apiSuccess {int} comments.id The comment id
 * @apiSuccess {string} comments.content The comment content
 * @apiSuccess {int} [comments.parent_id=null] The comment parent id (null for level 0)
 * @apiSuccess {int} comments.user_id The comment user_id (if not specified, use the current user id)
 * @apiSuccess {int} comments.commentable_id The commented model id
 * @apiSuccess {string=module,post,course} comments.commentable_type The commented model type
 * @apiSuccess {datetime} comments.created_at The created date
 * @apiSuccess {datetime} comments.updated_at The updated date
 * @apiSuccess {datetime} comments.deleted_at The deleted date
 * @apiSuccess {Comment[]} comments.children The comment children
 */
/**
 * @apiDefine Comment
 * @apiSuccess {int} id The comment id
 * @apiSuccess {string} content The comment content
 * @apiSuccess {int} [parent_id=null] The comment parent id (null for level 0)
 * @apiSuccess {int} user_id The comment user_id (if not specified, use the current user id)
 * @apiSuccess {int} commentable_id The commented model id
 * @apiSuccess {string=module,post,course} commentable_type The commented model type
 * @apiSuccess {datetime} created_at The created date
 * @apiSuccess {datetime} updated_at The updated date
 * @apiSuccess {datetime} deleted_at The deleted date
 * @apiSuccess {Comment[]} children The comment children
 */