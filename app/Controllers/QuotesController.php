<?php

namespace App\Controllers;

use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class QuotesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class QuotesController extends BaseController
{
    /**
     * GET /quotes/random/{count}
     * Get an amount of random quotes.
     *
     * @param int $count The number of quote to fetch.
     *
     * @return JsonResponse
     */
    public function random($count = 1)
    {
        $quotes = Quote::query()
            ->inRandomOrder()
            ->limit($count)
            ->get();

        return new JsonResponse($quotes);
    }
}
