/**
 * @apiDefine DisciplineData
 * @apiParam (Data) {string} name The discipline name
 * @apiParam (Data) {string} [slug=slug(name)] The discipline slug
 */
/**
 * @apiDefine DisciplineArray
 * @apiSuccess {Discipline[]} countries Array of countries
 * @apiSuccess {int} countries.id The discipline id
 * @apiSuccess {string} countries.name The discipline name
 * @apiSuccess {string} countries.slug The discipline slug
 */
/**
 * @apiDefine Discipline
 * @apiSuccess {int} id The discipline id
 * @apiSuccess {string} name The discipline name
 * @apiSuccess {string} slug The discipline slug
 */