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
 * @apiSuccess  {int} chapters.class_id The chapter class id
 * @apiSuccess {int} chapters.discipline_id The chapter discipline id
 */
/**
 * @apiDefine Chapter
 * @apiSuccess {int} id The chapter id
 * @apiSuccess {string} name The chapter name
 * @apiSuccess {string} slug The chapter slug
 * @apiSuccess {int} class_id The chapter class id
 * @apiSuccess {int} discipline_id The chapter discipline id
 */