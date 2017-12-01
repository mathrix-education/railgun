<?php
/**
 * @apiDefine SectionData
 * @apiParam (Data) {string} name The section name
 * @apiParam (Data) {string} slug The section slug
 * @apiParam (Data) {int} order The section order
 * @apiParam (Data) {int} training_id The section training id
 */
/**
 * @apiDefine SectionArray
 * @apiSuccess {Section[]} sections Array of countries
 * @apiSuccess {int} sections.id The section id
 * @apiSuccess {string} sections.name The section name
 * @apiSuccess {string} sections.slug The section slug
 * @apiSuccess {int} sections.order The section order
 * @apiSuccess {int} sections.training_id The section training id
 */
/**
 * @apiDefine Section
 * @apiSuccess {int} id The section id
 * @apiSuccess {string} name The section name
 * @apiSuccess {string} slug The section slug
 * @apiSuccess {int} order The section order
 * @apiSuccess {int} training_id The section training id
 */