<?php

declare(strict_types=1);

namespace App\Controllers;

use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

/**
 * Serve routes starting with /purchases.
 */
class PurchasesController extends BaseController
{
    /**
     * POST /purchases/checkout
     *
     * @return SuccessJsonResponse
     */
    public function checkout(): SuccessJsonResponse
    {

    }
}
