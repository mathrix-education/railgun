<?php
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
 * @apiSuccess {User} comments.user The user
 * @apiSuccess {int} comments.user.id The user id
 * @apiSuccess {string} comments.user.username The user username
 * @apiSuccess {string} comments.user.email The user email address
 * @apiSuccess {string} comments.user.firstname The user firstname
 * @apiSuccess {string} comments.user.lastname The user lastname
 * @apiSuccess {string} comments.user.facebook_token The facebook token
 * @apiSuccess {int} comments.user.points The user points
 * @apiSuccess {array} comments.user.settings The user settings
 * @apiSuccess {int} comments.user.settings.daily_goal The user daily goal points
 * @apiSuccess {bool} comments.user.settings.sound If the user has enabled sounds
 * @apiSuccess {bool} comments.user.settings.email_newsletter If the user should receive the newsletter
 * @apiSuccess {bool} comments.user.settings.email_comment If the user should receive an comment email
 * @apiSuccess {int} comments.user.class_id The user class_id
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
 * @apiSuccess {User} user The user
 * @apiSuccess {string} user.username The user username
 * @apiSuccess {string} user.email The user email address
 * @apiSuccess {string} user.firstname The user firstname
 * @apiSuccess {string} user.lastname The user lastname
 * @apiSuccess {string} user.facebook_token The facebook token
 * @apiSuccess {int} user.points The user points
 * @apiSuccess {array} user.settings The user settings
 * @apiSuccess {int} user.settings.daily_goal The user daily goal points
 * @apiSuccess {bool} user.settings.sound If the user has enabled sounds
 * @apiSuccess {bool} user.settings.email_newsletter If the user should receive the newsletter
 * @apiSuccess {bool} user.settings.email_comment If the user should receive an comment email
 * @apiSuccess {int} user.class_id The user class_id
 * @apiSuccess {Comment[]} children The comment children * @apiSuccess {int} comments.user.id The user id
 */