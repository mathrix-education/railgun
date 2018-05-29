<?php
/**
 * @apiDefine DiscountArray
 * @apiSuccess {Discount[]} discounts Array of discounts
 * @apiSuccess {string} discounts.code The discount code
 * @apiSuccess {int} discounts.counter The discount counter (-1 for infinite use)
 * @apiSuccess {int} discounts.id The discount id
 * @apiSuccess {string=absolute,percentage} discounts.type The discount type
 * @apiSuccess {int} discounts.value The discount value (in cents for `absolute`, in % for `percentage`)
 */
