<?php
namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class QuoteController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class QuoteController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /quotes/{?page}/{?perPage} Index
     * @apiGroup Quotes
     * @apiName QuotesIndex
     * @apiDescription Retrieve quotes
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse QuoteArray
     */


    /**
     * @apiVersion 0.8.1
     * @api {get} /quotes/{id} Get
     * @apiGroup Quotes
     * @apiName QuoteGet
     * @apiDescription Get a quote by id
     * @apiPermission public
     * @apiParam {int} id The quote id
     * @apiUse Quote
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 0.8.1
     * @api {post} /quotes Add
     * @apiGroup Quotes
     * @apiName QuoteAdd
     * @apiDescription Add a quote
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse QuoteData
     * @apiUse Quote
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */

    /**
     * @apiVersion 0.8.1
     * @api {patch} /quotes/{id} Edit
     * @apiGroup Quotes
     * @apiName QuoteEdit
     * @apiDescription Edit a quote
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The quote id
     * @apiUse QuoteData
     * @apiUse Quote
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 0.8.1
     * @api {delete} /quotes/{id} Delete
     * @apiGroup Quotes
     * @apiName QuoteDelete
     * @apiDescription Delete a quote
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The quote id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 0.8.1
     * @api {get} /quotes/random/{count} Random
     * @apiGroup Quotes
     * @apiName QuotesRandom
     * @apiDescription Get a set of random quotes ; use /quotes/index to get all quotes
     * @apiPermission public
     * @apiParam {int} [count=1] The quote count to fetch
     * @apiUse QuoteArray
     *
     * @param int $count
     *
     * @return JsonResponse
     */
    public function random($count = 1)
    {
        return response()->json(
            Quote::inRandomOrder()
                ->limit($count)
                ->get()
        );
    }
}
