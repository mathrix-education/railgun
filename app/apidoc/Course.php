<?php
/**
 * @apiDefine CourseData
 * @apiParam (Data) {string} title The course title
 * @apiParam (Data) {string} [slug=slug(title)] The course title
 * @apiParam (Data) {string} content The course content
 * @apiParam (Data) {int} category_id The course category id
 * @apiParam (Data) {int} user_id The course user id
 * @apiParam (Data) {datetime} [created_at] The course created date
 * @apiParam (Data) {datetime} [updated_at] The course updated date
 */
/**
 * @apiDefine CourseArray
 * @apiSuccess {Course[]} courses Array of courses
 * @apiSuccess {string} courses.title The course title
 * @apiSuccess {string} courses.slug The course title
 * @apiSuccess {string} courses.content The course content
 * @apiSuccess {int} courses.category_id The course category id
 * @apiSuccess {int} courses.user_id The course user id
 * @apiSuccess {datetime} courses.created_at The course created date
 * @apiSuccess {datetime} courses.updated_at The course updated date
 */
/**
 * @apiDefine Course
 * @apiSuccess {string} title The course title
 * @apiSuccess {string} slug The course title
 * @apiSuccess {string} content The course content
 * @apiSuccess {int} category_id The course category id
 * @apiSuccess {int} user_id The course user id
 * @apiSuccess {datetime} created_at The course created date
 * @apiSuccess {datetime} updated_at The course updated date
 */