<?php
/**
 * @apiDefine PostRequest
 * @apiParam (Data) {string} title The post title
 * @apiParam (Data) {string} [slug=slug(title)] The post title
 * @apiParam (Data) {string} content The post content
 * @apiParam (Data) {int} category_id The post category id
 * @apiParam (Data) {int} user_id The post user id
 * @apiParam (Data) {datetime} [created_at] The post created date
 * @apiParam (Data) {datetime} [updated_at] The post updated date
 */
