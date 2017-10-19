<?php
/**
 * @apiDefine ClassData
 * @apiParam (Data) {string} name The class name
 * @apiParam (Data) {string} [name_short=name] The class short name
 * @apiParam (Data) {string} [slug=slug(name)] The class slug
 * @apiParam (Data) {string} [slug_short=slug(name_short)] The class short slug
 * @apiParam (Data) {bool} visible The chapter visibility
 * @apiParam (Data) {int} country_id The class country id
 * @apiParam (Data) {array} link The models to link with the class
 * @apiParam (Data) {int[]} link.disciplines The discipline ids to link
 * @apiParam (Data) {array} unlink The models to unlink with the class
 * @apiParam (Data) {int[]} unlink.disciplines The discipline ids to unlink
 */
/**
 * @apiDefine ClassArray
 * @apiSuccess {Class[]} classes Array of classes
 * @apiSuccess {int} classes.id The class id
 * @apiSuccess {string} classes.name The class name
 * @apiSuccess {string} classes.name_short The class short name
 * @apiSuccess {string} classes.slug The class slug
 * @apiSuccess {string} classes.slug_short The class short slug
 * @apiSuccess {bool} classes.visible The chapter visibility
 * @apiSuccess {string} classes.country_id The class country id
 */
/**
 * @apiDefine Class
 * @apiSuccess {int} id The class id
 * @apiSuccess {string} name The class name
 * @apiSuccess {string} name_short The class short name
 * @apiSuccess {string} slug The class slug
 * @apiSuccess {string} slug_short The class short slug
 * @apiSuccess {bool} visible The class visibility
 * @apiSuccess {string} country_id The class country id
 */