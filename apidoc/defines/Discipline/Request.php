<?php
/**
 * @apiDefine DisciplineRequest
 * @apiParam (Data) {string} name The discipline name
 * @apiParam (Data) {string} slug The discipline slug
 * @apiParam (Data) {bool} [beta=0] The discipline beta
 * @apiParam (Data) {bool} [visible=1] The discipline visible
 * @apiParam (Data) {array} [link] The models to link with the chapter.
 * @apiParam (Data) {int[]} [link.classes] The classes ids to sync with the discipline.<br>
 * __Destructive__! Filling this field will erase old values.<br>
 * The given order wll be used to set the order in the many-to-many relationship.<br>
 * If not specified, do not change anything.
 */
