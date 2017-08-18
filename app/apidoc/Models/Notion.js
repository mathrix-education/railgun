/**
 * @apiDefine NotionData
 * @apiParam (Data) {string} name The notion name
 * @apiParam (Data) {string} [slug=slug(name)] The notion slug
 * @apiParam (Data) {int} chapter_id The notion chapter_id
 */
/**
 * @apiDefine NotionArray
 * @apiSuccess {Notion[]} notion Array of notions
 * @apiSuccess {int} notions.id The notion id
 * @apiSuccess {string} notions.name The notion name
 * @apiSuccess {string} notions.slug The notion slug
 * @apiSuccess {int} chapter_id The notion chapter_id
 */
/**
 * @apiDefine Notion
 * @apiSuccess {int} id The notion id
 * @apiSuccess {string} name The notion name
 * @apiSuccess {string} slug The notion slug
 * @apiSuccess {int} chapter_id The notion chapter_id
 */