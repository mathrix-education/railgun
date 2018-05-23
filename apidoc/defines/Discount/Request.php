<?php
/**
 * @apiDefine DiscountRequest
 * @apiParam (Data) {string} code The discount code
 * @apiParam (Data) {int} counter The discount counter
 * @apiParam (Data) {string=absolute,percentage} type The discount type
 * @apiParam (Data) {int} value The discount value (-1 for infinite use)
 */
