/**
 * @apiDefine oAuth
 * @apiSuccess {string=bearer} token_type
 * @apiSuccess {int} expires_in Time before token expiration in seconds
 * @apiSuccess {string} access_token The user access token; should be stored; valid until expiration
 * @apiSuccess {string} refresh_token The user refresh token; should be stored; see
 *     [/users/refresh](#api-Authentication-AuthenticationRefresh)
 */