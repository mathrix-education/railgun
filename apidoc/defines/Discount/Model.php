<?php
/**
 * @apiDefine DiscountModel
 * @apiSuccess {string} code The discount code
 * @apiSuccess {int} counter The discount counter (-1 for infinite use)
 * @apiSuccess {int} id The discount id
 * @apiSuccess {string=absolute,percentage} type The discount type
 * @apiSuccess {int} value The discount value (in cents for `absolute`, in % for `percentage`)
 */
