<?php
/**
 * @apiDefine UserData
 * @apiParam (Data) {string} [username=null] The user username
 * @apiParam (Data) {string} email The user email
 * @apiParam (Data) {string} [firstname=null] The user firstname
 * @apiParam (Data) {string} [lastname=null] The user lastname
 * @apiParam (Data) {string} password The user password
 * @apiParam (Data) {string=registered,moderator,admin} type The user type
 * @apiParam (Data) {int} points The user points
 * @apiParam (Data) {int} level The user level
 * @apiParam (Data) {int=10,20,40} daily The user daily goal
 * @apiParam (Data) {int} daily_achievements The user daily achievements
 * @apiParam (Data) {bool} premium If the user is premium or not
 * @apiParam (Data) {array} settings The user options
 * @apiParam (Data) {string} [settings.facebook_id] If the user has a facebook id
 * @apiParam (Data) {bool} settings.sound If the user has enabled sounds
 * @apiParam (Data) {string} [stripe_id=null] The Stripe customer id
 * @apiParam (Data) {string} [card_brand=null] The card brand (Visa, etc.)
 * @apiParam (Data) {string} [card_last_four=null] The card last four digits
 * @apiParam (Data) {string} [trial_ends_at=null] The trial expire date
 */
/**
 * @apiDefine UserArray
 * @apiSuccess {User[]} users Array of users
 * @apiSuccess {string} users.id The user id
 * @apiSuccess {string} users.username The user username
 * @apiSuccess {string} users.email The user email
 * @apiSuccess {string} users.firstname The user firstname
 * @apiSuccess {string} users.lastname The user lastname
 * @apiSuccess {string} users.password The user password
 * @apiSuccess {string=registered,moderator,admin} users.type The user type
 * @apiSuccess {int} users.points The user points
 * @apiSuccess {int} users.level The user level
 * @apiSuccess {int=10,20,40} users.daily The user daily goal
 * @apiSuccess {int} users.daily_achievements The user daily achievements
 * @apiSuccess {bool} users.premium If the user is premium or not
 * @apiSuccess {array} users.settings The user options
 * @apiSuccess {bool} users.settings.sound If the user has enabled sounds
 * @apiSuccess {string} users.stripe_id The Stripe customer id
 * @apiSuccess {string} users.card_brand The card brand (Visa, etc.)
 * @apiSuccess {string} users.card_last_four The card last four digits
 * @apiSuccess {string} users.trial_ends_at The trial expire date
 */
/**
 * @apiDefine User
 * @apiSuccess {string} id The user id
 * @apiSuccess {string} username The user username
 * @apiSuccess {string} email The user email
 * @apiSuccess {string} firstname The user firstname
 * @apiSuccess {string} lastname The user lastname
 * @apiSuccess {string} password The user password
 * @apiSuccess {string=registered,moderator,admin} type The user type
 * @apiSuccess {int} points The user points
 * @apiSuccess {int} level The user level
 * @apiSuccess {int=10,20,40} daily The user daily goal
 * @apiSuccess {int} daily_achievements The user daily achievements
 * @apiSuccess {bool} premium If the user is premium or not
 * @apiSuccess {array} settings The user options
 * @apiSuccess {bool} settings.sound If the user has enabled sounds
 * @apiSuccess {string} stripe_id The Stripe customer id
 * @apiSuccess {string} card_brand The card brand (Visa, etc.)
 * @apiSuccess {string} card_last_four The card last four digits
 * @apiSuccess {string} trial_ends_at The trial expire date
 */