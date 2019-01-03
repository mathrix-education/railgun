<?php
namespace App\Controllers;

use App\Models\Quote;
use Illuminate\Http\JsonResponse;

/**
 * Class QuoteController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class QuoteController extends BaseController
{
    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /quotes/{?page}/{?perPage} Index
     * @apiGroup       Quotes
     * @apiName        QuotesIndex
     * @apiDescription Retrieve quotes
     * @apiPermission  public
     * @apiUse         Pagination
     * @apiUse         QuoteArray
     *
     * @param mixed $count
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /quotes/{id} Get
     * @apiGroup       Quotes
     * @apiName        QuoteGet
     * @apiDescription Get a quote by id
     * @apiPermission  public
     * @apiParam {int} id The quote id
     * @apiUse         QuoteModel
     * @apiUse         Http404ModelNotFound
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {post} /quotes Add
     * @apiGroup       Quotes
     * @apiName        QuoteAdd
     * @apiDescription Add a quote
     * @apiPermission  admin
     * @apiUse         Bearer
     * @apiUse         QuoteRequest
     * @apiUse         QuoteModel
     * @apiUse         Http401Unauthorized
     * @apiUse         Http422ModelValidation
     * @apiUse         Http422ModelSave
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {patch} /quotes/{id} Edit
     * @apiGroup       Quotes
     * @apiName        QuoteEdit
     * @apiDescription Edit a quote
     * @apiPermission  admin
     * @apiUse         Bearer
     * @apiParam {int} id The quote id
     * @apiUse         QuoteRequest
     * @apiUse         QuoteModel
     * @apiUse         Http401Unauthorized
     * @apiUse         Http404ModelNotFound
     * @apiUse         Http422ModelSave
     * @apiUse         Http422ModelValidation
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {delete} /quotes/{id} Delete
     * @apiGroup       Quotes
     * @apiName        QuoteDelete
     * @apiDescription Delete a quote
     * @apiUse         Bearer
     * @apiPermission  admin
     * @apiParam {int} id The quote id
     * @apiUse         Http401Unauthorized
     * @apiUse         Http404ModelNotFound
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /quotes/random/{count} Random
     * @apiGroup       Quotes
     * @apiName        QuotesRandom
     * @apiDescription Get a set of random quotes ; use /quotes/index to get all quotes
     * @apiPermission  public
     * @apiParam {int} [count=1] The quote count to fetch
     * @apiUse         QuoteArray
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
