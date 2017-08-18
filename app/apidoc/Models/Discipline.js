/**
 * @apiDefine DisciplineData
 * @apiParam (Data) {string} name The discipline name
 * @apiParam (Data) {string} [slug=slug(name)] The discipline slug
 */
/**
 * @apiDefine DisciplineArray
 * @apiSuccess {Discipline[]} disciplines Array of disciplines
 * @apiSuccess {int} disciplines.id The discipline id
 * @apiSuccess {string} disciplines.name The discipline name
 * @apiSuccess {string} disciplines.slug The discipline slug
 */
/**
 * @apiDefine Discipline
 * @apiSuccess {int} id The discipline id
 * @apiSuccess {string} name The discipline name
 * @apiSuccess {string} slug The discipline slug
 */