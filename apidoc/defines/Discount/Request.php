<?php
/**
 * @apiDefine DiscountRequest
 * @apiParam (Data) {string} code The discount code
 * @apiParam (Data) {int} counter The discount counter (-1 for infinite use)
 * @apiParam (Data) {string=absolute,percentage} type The discount type
 * @apiParam (Data) {int} value The discount value (in cents for `absolute`, in % for `percentage`)
 */
