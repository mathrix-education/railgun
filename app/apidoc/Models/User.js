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
 * @apiSuccess {string} created_at The user creation date; Y-m-d H:i:s
 * @apiSuccess {string} updated_at The user creation date; Y-m-d H:i:s
 * @apiSuccess {int} class_id The user class_id
 */