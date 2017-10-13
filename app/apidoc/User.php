<?php
/**
 * @apiDefine UserData
 * @apiParam (Data) {string} [firstname=null] The user firstname
 * @apiParam (Data) {string} [lastname=null] The user lastname
 * @apiParam (Data) {string} [username=null] The user username
 * @apiParam (Data) {string=registered,premium,moderator,admin} type The user type
 * @apiParam (Data) {string} email The user email
 * @apiParam (Data) {string} password The user password
 * @apiParam (Data) {int} [class_id=null] The class id; should be `null` for `moderator` and `admin`
 * @apiParam (Data) {int=20,40,70} daily The user daily goal
 * @apiParam (Data) {array} settings The user options
 * @apiParam (Data) {bool} settings.sound If the user has enabled sounds
 * @apiParam (Data) {bool} settings.email_newsletter If the user should receive the newsletter
 * @apiParam (Data) {bool} settings.email_comment If the user should receive an comment email
 */
/**
 * @apiDefine User
 * @apiSuccess {int} id The user id
 * @apiSuccess {string} username The user username
 * @apiSuccess {string} email The user email address
 * @apiSuccess {string} firstname The user firstname
 * @apiSuccess {string} lastname The user lastname
 * @apiSuccess {string} facebook_token The facebook token
 * @apiSuccess {int} points The user points
 * @apiSuccess {array} settings The user settings
 * @apiSuccess {int} settings.daily_goal The user daily goal points
 * @apiSuccess {bool} settings.sound If the user has enabled sounds
 * @apiSuccess {bool} settings.email_newsletter If the user should receive the newsletter
 * @apiSuccess {bool} settings.email_comment If the user should receive an comment email
 * @apiSuccess {int} class_id The user class_id
 */
/**
 * @apiDefine UserArray
 * @apiSuccess {User[]} users Array of users
 * @apiSuccess {int} users.id The user id
 * @apiSuccess {string} users.username The user username
 * @apiSuccess {string} users.email The user email address
 * @apiSuccess {string} users.firstname The user firstname
 * @apiSuccess {string} users.lastname The user lastname
 * @apiSuccess {string} users.facebook_token The facebook token
 * @apiSuccess {int} users.points The user points
 * @apiSuccess {array} users.settings The user settings
 * @apiSuccess {int} users.settings.daily_goal The user daily goal points
 * @apiSuccess {bool} users.settings.sound If the user has enabled sounds
 * @apiSuccess {bool} users.settings.email_newsletter If the user should receive the newsletter
 * @apiSuccess {bool} users.settings.email_comment If the user should receive an comment email
 * @apiSuccess {int} users.class_id The user class_id
 */