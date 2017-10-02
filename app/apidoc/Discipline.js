/**
 * @apiDefine DisciplineData
 * @apiParam (Data) {string} name The discipline name
 * @apiParam (Data) {string} [slug=slug(name)] The discipline slug
 * @apiParam (Data) {bool} [beta=false] If the discipline is in beta
 * @apiParam (Data) {string} image The discipline image
 * @apiParam (Data) {array} link The models to link with the discipline
 * @apiParam (Data) {int[]} link.classes The class ids to link
 * @apiParam (Data) {array} unlink The models to unlink with the discipline
 * @apiParam (Data) {int[]} unlink.classes The class ids to unlink
 */
/**
 * @apiDefine DisciplineArray
 * @apiSuccess {Discipline[]} disciplines Array of disciplines
 * @apiSuccess {int} disciplines.id The discipline id
 * @apiSuccess {string} disciplines.name The discipline name
 * @apiSuccess {string} disciplines.slug The discipline slug
 * @apiSuccess {bool} disciplines.beta If the discipline is in beta
 * @apiSuccess {string} disciplines.image The discipline image
 */
/**
 * @apiDefine Discipline
 * @apiSuccess {int} id The discipline id
 * @apiSuccess {string} name The discipline name
 * @apiSuccess {string} slug The discipline slug
 * @apiSuccess {bool} beta If the discipline is in beta
 * @apiSuccess {string} image The discipline image
 */