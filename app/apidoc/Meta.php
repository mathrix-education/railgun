<?php
/**
 * @apiDefine MetaData
 * @apiParam (Data) {bool} [favorite=false] If the activity is a favorite
 * @apiParam (Data) {string=completed,to_revise,null} [status=null] If the activity is a favorite
 * @apiParam (Data) {int} country_id The meta country_id
 * @apiParam (Data) {int} class_id_id The meta class_id_id
 * @apiParam (Data) {int} discipline_id The meta discipline_id
 * @apiParam (Data) {int} chapter_id The meta chapter_id
 * @apiParam (Data) {int} notion_id The meta notion_id
 * @apiParam (Data) {int} activity_id The meta activity_id
 */
/**
 * @apiDefine MetaArray
 * @apiSuccess {Meta[]} metas Array of metas
 * @apiSuccess {int} metas.id The meta id
 * @apiSuccess {bool} metas.favorite If the activity is a favorite
 * @apiSuccess {string=completed,to_revise,null} metas.status The meta status
 * @apiSuccess {int} metas.country_id The meta country_id
 * @apiSuccess {int} metas.class_id_id The meta class_id_id
 * @apiSuccess {int} metas.discipline_id The meta discipline_id
 * @apiSuccess {int} metas.chapter_id The meta chapter_id
 * @apiSuccess {int} metas.notion_id The meta notion_id
 * @apiSuccess {int} metas.activity_id The meta activity_id
 * @apiSuccess {Country} metas.country The meta country
 * @apiSuccess {Class} metas.class The meta class
 * @apiSuccess {Discipline} metas.discipline The meta discipline
 * @apiSuccess {Chapter} metas.chapter The meta chapter
 * @apiSuccess {Notion} metas.notion The meta notion
 * @apiSuccess {Activity} metas.activity The meta activity
 */
/**
 * @apiDefine Meta
 * @apiSuccess {int} id The meta id
 * @apiSuccess {bool} favorite If the activity is a favorite
 * @apiSuccess {string=completed,to_revise,null} status The meta status
 * @apiSuccess {int} country_id The meta country_id
 * @apiSuccess {int} class_id_id The meta class_id_id
 * @apiSuccess {int} discipline_id The meta discipline_id
 * @apiSuccess {int} chapter_id The meta chapter_id
 * @apiSuccess {int} notion_id The meta notion_id
 * @apiSuccess {int} activity_id The meta activity_id
 * @apiSuccess {Country} country The meta country
 * @apiSuccess {Class} class The meta class
 * @apiSuccess {Discipline} discipline The meta discipline
 * @apiSuccess {Chapter} chapter The meta chapter
 * @apiSuccess {Notion} notion The meta notion
 * @apiSuccess {Activity} activity The meta activity
 */