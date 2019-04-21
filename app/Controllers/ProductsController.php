<?php

namespace App\Controllers;

use App\Exceptions\DiscountExpiredException;
use App\Exceptions\DiscountNotApplicableException;
use App\Exceptions\InvalidBraintreeCustomerException;
use App\Exceptions\InvalidSponsorException;
use App\Exceptions\ProductAlreadyBoughtException;
use App\Exceptions\ProductNotBuyableException;
use App\Exceptions\SponsorNotFoundException;
use App\Models\Product;
use App\Services\Braintree\Exceptions\BraintreeGatewayException;
use App\Services\Braintree\Exceptions\BraintreeProcessorException;
use App\Services\Braintree\Exceptions\DuplicatePaymentMethodNonceException;
use App\Services\Braintree\Pipelines\CartPipeline;
use App\Services\Braintree\Utils\AffiliateProcess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\ValidationException;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Class ProductsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class ProductsController extends BaseController
{
    /**
     * PATCH /products/{packId}/items
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $packId The pack id.
     *
     * @return JsonResponse
     */
    public function items(Request $request, int $packId)
    {
        /** @var Product $pack */
        $pack = Product::query()
            ->where("id", "=", $packId)
            ->where("type", "=", "pack")
            ->firstOrFail();

        $itemsSync = collect($request->all())
            ->mapWithKeys(function (int $itemId, int $order) {
                return [$itemId => ["order" => $order]];
            });

        $pack->items()->sync($itemsSync);

        return new JsonResponse($pack->load("items"));
    }


    /**
     * GET /products/slug/{productSlug}
     *
     * @param string $productSlug The products slug.
     *
     * @return JsonResponse
     */
    public function getBySlug(string $productSlug)
    {
        $product = Product::query()
            ->where("slug", "=", $productSlug)
            ->firstOrFail();

        return new JsonResponse($product);
    }


    /**
     * GET /products/{type}/{page}/{perPage}
     * Get products of a given type.
     *
     * @param string $type all|training|section|lesson The product types.
     * @param int $page The page (starts at 0).
     * @param int $perPage The number of product per page.
     *
     * @return PaginationJsonResponse
     */
    public function paginateByType(string $type, int $page, int $perPage): PaginationJsonResponse
    {
        $query = Product::query()->where("type", "=", $type);

        return new PaginationJsonResponse($query, $page, $perPage);
    }


    /**
     * GET /products/buyable/{type}/{page}/{perPage}
     * Get buyable products.
     *
     * @param string $type all|training|section|lesson The product types.
     * @param int $page The page (starts at 0).
     * @param int $perPage The number of product per page.
     *
     * @return PaginationJsonResponse
     */
    public function buyable($type, int $page, int $perPage): PaginationJsonResponse
    {
        $query = Product::with("children")
            ->whereNotNull("price");

        if ($type !== "all") {
            $query = $query->where("type", "=", $type);
        }

        return new PaginationJsonResponse($query, $page, $perPage);
    }


    /**
     * GET /products/{productId}/recursive
     * Get a product with its children, recursively.
     *
     * @param int $productId The product id.
     *
     * @return JsonResponse
     */
    public function recursive(int $productId): JsonResponse
    {
        $maxDepth = 3;
        $with = ["children"];

        for ($i = 1; $i <= $maxDepth; $i++) {
            $with[] = $with[$i - 1] . ".children";
        }

        foreach ($with as $relation) {
            $with[] = "items.$relation";
        }

        $withModules = [];
        foreach ($with as $relation) {
            $withModules[] = $relation . ".module";
        }

        $with = array_merge($with, $withModules);
        $product = Product::with($with)->findOrFail($productId);

        return new JsonResponse($product);
    }


    /**
     * POST /products/buy
     * Buy products with Braintree.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws BraintreeGatewayException
     * @throws BraintreeProcessorException
     * @throws DiscountExpiredException
     * @throws DiscountNotApplicableException
     * @throws DuplicatePaymentMethodNonceException
     * @throws InvalidBraintreeCustomerException
     * @throws InvalidSponsorException
     * @throws ProductAlreadyBoughtException
     * @throws ProductNotBuyableException
     * @throws SponsorNotFoundException
     * @throws ValidationException
     */
    public function buy(Request $request): JsonResponse
    {
        return (new CartPipeline($request))->process();
    }


    /**
     * POST /products/sponsor
     * Check sponsor email validity.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws SponsorNotFoundException
     * @throws InvalidSponsorException
     */
    public function sponsor(Request $request): JsonResponse
    {
        $this->validate($request, [
            "sponsor_email" => "required"
        ]);

        $user = $request->user();
        $sponsorEmail = $request->json("sponsor_email");

        $affiliateProcess = new AffiliateProcess($user, $sponsorEmail);
        $affiliateProcess->canBeAffiliatedOrFail();

        return new JsonResponse([
            "discount" => $affiliateProcess->getDiscountAmount()
        ]);
    }
}
