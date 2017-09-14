/**
 * @apiDefine PostData
 * @apiParam (Data) {string} title The post title
 * @apiParam (Data) {string} [slug=slug(title)] The post title
 * @apiParam (Data) {string} content The post content
 * @apiParam (Data) {int} category_id The post category id
 * @apiParam (Data) {int} user_id The post user id
 * @apiParam (Data) {datetime} [created_at] The post created date
 * @apiParam (Data) {datetime} [updated_at] The post updated date
 */
/**
 * @apiDefine PostArray
 * @apiSuccess {Post[]} posts Array of posts
 * @apiSuccess {string} posts.title The post title
 * @apiSuccess {string} posts.slug The post title
 * @apiSuccess {string} posts.content The post content
 * @apiSuccess {int} posts.category_id The post category id
 * @apiSuccess {int} posts.user_id The post user id
 * @apiSuccess {datetime} posts.created_at The post created date
 * @apiSuccess {datetime} posts.updated_at The post updated date
 */
/**
 * @apiDefine Post
 * @apiSuccess {string} title The post title
 * @apiSuccess {string} slug The post title
 * @apiSuccess {string} content The post content
 * @apiSuccess {int} category_id The post category id
 * @apiSuccess {int} user_id The post user id
 * @apiSuccess {datetime} created_at The post created date
 * @apiSuccess {datetime} updated_at The post updated date
 */