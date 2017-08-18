/**
 * @apiDefine CountryData
 * @apiParam (Data) {string} name The country name
 * @apiParam (Data) {string} [name_short=name] The country short name
 * @apiParam (Data) {string} [slug=slug(name)] The country slug
 * @apiParam (Data) {string} [slug_short=slug(name_short)] The country short slug
 */
/**
 * @apiDefine CountryArray
 * @apiSuccess {Country[]} countries Array of countries
 * @apiSuccess {int} countries.id The country id
 * @apiSuccess {string} countries.name The country name
 * @apiSuccess {string} countries.name_short The country short name
 * @apiSuccess {string} countries.slug The country slug
 * @apiSuccess {string} countries.slug_short The country short slug
 */
/**
 * @apiDefine Country
 * @apiSuccess {int} id The country id
 * @apiSuccess {string} name The country name
 * @apiSuccess {string} name_short The country short name
 * @apiSuccess {string} slug The country slug
 * @apiSuccess {string} slug_short The country short slug
 */