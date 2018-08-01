<?php
/**
 * @apiDefine UserArray
 * @apiSuccess {User[]} users Array of users
 * @apiSuccess {string} users.id The user id
 * @apiSuccess {string} users.username The user username
 * @apiSuccess {string} users.email The user email
 * @apiSuccess {string} users.firstname The user firstname
 * @apiSuccess {string} users.lastname The user lastname
 * @apiSuccess {string} users.password The user password
 * @apiSuccess {string=registered,redactor,moderator,admin} users.type The user type
 * @apiSuccess {int} users.points The user points
 * @apiSuccess {int} users.level The user level
 * @apiSuccess {int=10,20,40} users.daily The user daily goal
 * @apiSuccess {int} users.daily_achievements The user daily achievements
 * @apiSuccess {bool} users.premium If the user is premium or not
 * @apiSuccess {array} users.settings The user options
 * @apiSuccess {bool} users.settings.sound If the user has enabled sounds
 * @apiSuccess {string} [users.settings.facebook_id] If the user has a facebook id
 * @apiSuccess {string} users.stripe_id The Stripe customer id
 * @apiSuccess {string} users.card_brand The card brand (Visa, etc.)
 * @apiSuccess {string} users.card_last_four The card last four digits
 * @apiSuccess {string} users.trial_ends_at The trial expire date
 */
