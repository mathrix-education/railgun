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
 * @since 0.9a-1
 * @copyright Mathrix
 */
class QuoteController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /quotes Index
     * @apiGroup Quotes
     * @apiName QuotesIndex
     * @apiDescription Retrieve quotes
     * @apiPermission public
     * @apiUse QuoteArray
     * @return JsonResponse
     */
    public function index()
    {
        return $this->http200Success(Quote::all());
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /quotes/{id} Get
     * @apiGroup Quotes
     * @apiName QuoteGet
     * @apiDescription Get a quote by id
     * @apiPermission public
     * @apiParam {int} id The quote id
     * @apiUse Quote
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $quote = Quote::where('id', $id)->firstOrFail();

        return $this->http200Success($quote);
    }

    /**
     * @apiVersion 1.0.0
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
        return $this->http200Success(
            Quote::inRandomOrder()
                ->limit($count)
                ->get()
        );
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /quotes Add
     * @apiGroup Quotes
     * @apiName QuoteAdd
     * @apiDescription Add a quote
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse QuoteData
     * @apiUse Quote
     * @apiUse 401Unauthorized
     * @apiUse 422Validation
     * @apiUse 422Save
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        if ($request->user()->cant('add', Quote::class)) {
            return $this->http401Unauthorized();
        }

        $quote = new Quote();
        $quote->fill($request->all());

        if (!$quote->save()) {
            return $quote->handleResponse($this->http422Save());
        }

        return $this->http200Success($quote);
    }


    /**
     * @apiVersion 1.0.0
     * @api {patch} /quotes/{id} Edit
     * @apiGroup Quotes
     * @apiName QuoteEdit
     * @apiDescription Edit a quote
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The quote id
     * @apiUse QuoteData
     * @apiUse Quote
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     * @apiUse 422Save
     * @apiUse 422Validation
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function edit(Request $request, int $id)
    {
        /** @var Quote $quote */
        $quote = Quote::where('id', $id)->firstOrFail();

        if ($request->user()->cant('edit', $quote)) {
            return $this->http401Unauthorized();
        }

        $quote->fill($request->all());

        if (!$quote->save()) {
            return $quote->handleResponse($this->http422Save());
        }

        return $this->http200Success($quote);
    }


    /**
     * @apiVersion 1.0.0
     * @api {delete} /quotes/{id} Delete
     * @apiGroup Quotes
     * @apiName QuoteDelete
     * @apiDescription Delete a quote
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The quote id
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function delete(Request $request, int $id)
    {
        /** @var Quote $quote */
        $quote = Quote::where('id', '=', $id)->firstOrFail();

        if ($request->user()->cant('delete', $quote)) {
            return $this->http401Unauthorized();
        }

        if (!$quote->delete()) {
            return $quote->handleResponse($this->http422Delete());
        }

        return $this->http200Success();
    }
}
