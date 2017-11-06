<?php
/**
 * @apiDefine ReportData
 * @apiParam (Data) {string=todo,done,validated} status The report status
 * @apiParam (Data) {string} content The report content
 * @apiParam (Data) {int} module_id The report module id
 */
/**
 * @apiDefine ReportArray
 * @apiSuccess {int} reports.id The report id
 * @apiSuccess {string=todo,done,validated} reports.status The report status
 * @apiSuccess {string} reports.content The report content
 * @apiSuccess {int} reports.module_id The report module id
 */
/**
 * @apiDefine Report
 * @apiSuccess {int} id The report id
 * @apiSuccess {string=todo,done,validated} status The report status
 * @apiSuccess {string} content The report content
 * @apiSuccess {int} module_id The report module id
 */