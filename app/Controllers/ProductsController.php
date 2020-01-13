<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Bishop\Invoice;
use App\Services\Stripe\Pipelines\ProductsCheckoutPipeline;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\DataResponse;

/**
 * Serve routes starting with /products.
 */
class ProductsController extends BaseController
{
    /**
     * @return DataResponse
     */
    public function checkout(): DataResponse
    {
        $pipeline = (new ProductsCheckoutPipeline())->flow();
        $data     = Invoice::from($pipeline->invoice);
        $status   = $data->status === \Stripe\Invoice::STATUS_OPEN ? 202 : 200;

        return new DataResponse($data, [], $status);
    }
}
