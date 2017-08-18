/**
 * @apiDefine ChapterData
 * @apiParam (Data) {string} name The chapter name
 * @apiParam (Data) {string} [slug=slug(name)] The chapter slug
 * @apiParam (Data) {int} class_id The chapter class id
 * @apiParam (Data) {int} discipline_id The chapter discipline id
 */
/**
 * @apiDefine ChapterArray
 * @apiSuccess {Chapter[]} chapters Array of chapters
 * @apiSuccess {int} chapters.id The chapter id
 * @apiSuccess {string} chapters.name The chapter name
 * @apiSuccess {string} chapters.slug The chapter slug
 * @apiParam  {int} chapters.class_id The chapter class id
 * @apiParam {int} chapters.discipline_id The chapter discipline id
 */
/**
 * @apiDefine Chapter
 * @apiSuccess {int} id The chapter id
 * @apiSuccess {string} name The chapter name
 * @apiSuccess {string} slug The chapter slug
 * @apiParam {int} class_id The chapter class id
 * @apiParam {int} discipline_id The chapter discipline id
 */