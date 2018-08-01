<?php
/**
 * @apiDefine UserRequest
 * @apiParam (Data) {string} [username=null] The user username
 * @apiParam (Data) {string} email The user email
 * @apiParam (Data) {string} [firstname=null] The user firstname
 * @apiParam (Data) {string} [lastname=null] The user lastname
 * @apiParam (Data) {string} password The user password
 * @apiParam (Data) {string=registered,redactor,moderator,admin} type The user type
 * @apiParam (Data) {int} points The user points
 * @apiParam (Data) {int} level The user level
 * @apiParam (Data) {int=10,20,40} daily The user daily goal
 * @apiParam (Data) {int} daily_achievements The user daily achievements
 * @apiParam (Data) {bool} premium If the user is premium or not
 * @apiParam (Data) {array} settings The user options
 * @apiParam (Data) {bool} settings.sound If the user has enabled sounds
 * @apiParam (Data) {string} [settings.facebook_id] If the user has a facebook id
 * @apiParam (Data) {string} [stripe_id=null] The Stripe customer id
 * @apiParam (Data) {string} [card_brand=null] The card brand (Visa, etc.)
 * @apiParam (Data) {string} [card_last_four=null] The card last four digits
 * @apiParam (Data) {string} [trial_ends_at=null] The trial expire date
 * @apiParam (Data) {array} [link] The models to link with the users
 * @apiParam (Data) {int[]} [link.trainings] The trainings ids to sync with the activity.<br>
 * __Destructive__! Filling this field will erase old values. Only admins can set this value.<br>
 * If not specified, do not change anything.
 */
