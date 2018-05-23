<?php
/**
 * @apiDefine DiscountArray
 * @apiSuccess {Discount[]} discounts Array of discounts
 * @apiSuccess {string} discounts.code The discount code
 * @apiSuccess {int} discounts.counter The discount counter
 * @apiSuccess {int} discounts.id The discount id
 * @apiSuccess {string=absolute,percentage} discounts.type The discount type
 * @apiSuccess {int} discounts.value The discount value (-1 for infinite use)
 */
