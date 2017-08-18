/**
 * @apiDefine ModuleData
 * @apiParam (Data) {string} name The module name
 * @apiParam (Data) {string} [slug=slug(name)] The module slug
 * @apiParam (Data) {string} description The module description
 * @apiParam (Data) {array} link The models to link with the notion
 * @apiParam (Data) {int[]} link.activities The activities ids to link
 * @apiParam (Data) {array} unlink The models to unlink with the notion
 * @apiParam (Data) {int[]} unlink.activities The activities ids to unlink
 */
/**
 * @apiDefine ModuleArray
 * @apiSuccess {Module[]} module Array of modules
 * @apiSuccess {int} modules.id The module id
 * @apiSuccess {string} modules.name The module name
 * @apiSuccess {string} modules.slug The module slug
 * @apiSuccess {string} modules.description The module description
 */
/**
 * @apiDefine Module
 * @apiSuccess {int} id The module id
 * @apiSuccess {string} name The module name
 * @apiSuccess {string} slug The module slug
 * @apiSuccess {string} description The module description
 */