/**
 * @apiDefine ActivityData
 * @apiParam (Data) {string} name The activity name
 * @apiParam (Data) {string} [slug=slug(name)] The activity slug
 * @apiParam (Data) {string} description The activity description
 */
/**
 * @apiDefine ActivityArray
 * @apiSuccess {Activity[]} activity Array of activities
 * @apiSuccess {int} activities.id The activity id
 * @apiSuccess {string} activities.name The activity name
 * @apiSuccess {string} activities.slug The activity slug
 * @apiSuccess {string} activities.description The activity description
 */
/**
 * @apiDefine Activity
 * @apiSuccess {int} id The activity id
 * @apiSuccess {string} name The activity name
 * @apiSuccess {string} slug The activity slug
 * @apiSuccess {string} description The activity description
 */