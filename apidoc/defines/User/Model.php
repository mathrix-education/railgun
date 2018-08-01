<?php
/**
 * @apiDefine UserModel
 * @apiSuccess {string} id The user id
 * @apiSuccess {string} username The user username
 * @apiSuccess {string} email The user email
 * @apiSuccess {string} firstname The user firstname
 * @apiSuccess {string} lastname The user lastname
 * @apiSuccess {string} password The user password
 * @apiSuccess {string=registered,redactor,moderator,admin} type The user type
 * @apiSuccess {int} points The user points
 * @apiSuccess {int} level The user level
 * @apiSuccess {int=10,20,40} daily The user daily goal
 * @apiSuccess {int} daily_achievements The user daily achievements
 * @apiSuccess {bool} premium If the user is premium or not
 * @apiSuccess {array} settings The user options
 * @apiSuccess {bool} settings.sound If the user has enabled sounds
 * @apiSuccess {string} [settings.facebook_id] If the user has a facebook id
 * @apiSuccess {string} stripe_id The Stripe customer id
 * @apiSuccess {string} card_brand The card brand (Visa, etc.)
 * @apiSuccess {string} card_last_four The card last four digits
 * @apiSuccess {string} trial_ends_at The trial expire date
 */
