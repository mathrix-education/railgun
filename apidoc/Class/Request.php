<?php
/**
 * @apiDefine ClassRequest
 * @apiParam (Data) {string} name The class name
 * @apiParam (Data) {string} name_short The class name_short
 * @apiParam (Data) {string} slug The class slug
 * @apiParam (Data) {string} slug_short The class slug_short
 * @apiParam (Data) {int} [order=0] The class order
 * @apiParam (Data) {bool} [visible=1] The class visible
 * @apiParam (Data) {int-null} [parent_id=null] The class parent_id
 * @apiParam (Data) {int} country_id The class country_id
 * @apiParam (Data) {array} [link] The models to link with the class.
 * @apiParam (Data) {int[]} [link.disciplines] The disciplines ids to sync with the class. __Destructive__! Filling this
 * field will erase old values.If not specified, do not change anything.
 */
