<?php

namespace App\Controllers;

use App\Exceptions\DiscountExpiredException;
use App\Exceptions\DiscountNotApplicableException;
use App\Exceptions\ImmutableException;
use App\Exceptions\InvalidBraintreeCustomerException;
use App\Exceptions\InvalidSponsorException;
use App\Exceptions\ProductAlreadyBoughtException;
use App\Exceptions\ProductNotBuyableException;
use App\Exceptions\SponsorNotFoundException;
use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use App\Services\Braintree\Exceptions\BraintreeGatewayException;
use App\Services\Braintree\Exceptions\BraintreeProcessorException;
use App\Services\Braintree\Exceptions\DuplicatePaymentMethodNonceException;
use App\Services\Braintree\Pipelines\CartPipeline;
use App\Services\Braintree\Utils\AffiliateProcess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
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
        // TODO: Add validator
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
    public function buyable(string $type, int $page, int $perPage): PaginationJsonResponse
    {
        $query = Product::with("children")
            ->where("buyable", "=", true);

        if ($type !== "all") {
            $query = $query->where("type", "=", $type)
                ->where("visible", "=", true);
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
     * @throws ImmutableException
     */
    public function buy(Request $request): JsonResponse
    {
        return (new CartPipeline($request))->process();
    }


    /**
     * GET /discounts/code/{discountCode}
     * Check if a discount is valid.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws DiscountExpiredException
     * @throws DiscountNotApplicableException
     * @throws InvalidSponsorException
     * @throws ProductAlreadyBoughtException
     * @throws ProductNotBuyableException
     * @throws SponsorNotFoundException
     * @throws ValidationException
     * @throws Http401UnauthorizedException
     */
    public function cart(Request $request)
    {
        // We not ne check id existence here since we got specialized exceptions
        $this->validate($request, [
            "cart" => "required",
            "cart.*" => "distinct|integer|exists:products,id",
            "discount_code" => "nullable|exists:discounts,code",
            "sponsor_email" => "nullable|string"
        ]);

        /** @var User|null $customer */
        $customer = $request->user();
        /** @var int[] $productIds */
        $productIds = $request->json("cart");
        /** @var Collection|Product[] $products */
        $products = Product::query()->whereIn("id", $productIds)->get()->unique();
        /** @var Discount|null $discount */
        $discount = null;

        // Check discount code if any
        if ($request->json("discount_code") !== null) {
            $discount = Discount::query()
                ->where("code", "=", $request->json("discount_code"))
                ->firstOrFail();

            if (!$discount->isApplicableBleOnAtLeastOneOf($products)) {
                throw new DiscountNotApplicableException($discount);
            } else if ($discount->isExpired()) {
                throw new DiscountExpiredException($discount);
            }
        }

        // Check products integrity
        foreach ($products as $product) {
            if ($customer !== null && $customer->hasPurchased($product)) {
                throw new ProductAlreadyBoughtException($customer, $product);
            } else if ($product->price === null) {
                throw  new ProductNotBuyableException($product);
            }
        }

        $lines = $products->map(function (Product $product) use ($customer, $discount) {
            return array_merge([
                "name" => $product->name,
                "type" => $product->type,
                "description" => $product->description,
                "price" => $product->price,
                "price_after_discount" => $product->getNewPrice($discount)
            ]);
        });

        // Check sponsor email if any
        if ($request->json("sponsor_email") !== null) {
            if ($customer === null) {
                throw new Http401UnauthorizedException();
            }

            $sponsorEmail = $request->json("sponsor_email");
            $affiliateProcess = new AffiliateProcess($customer, $sponsorEmail);
            $affiliateProcess->canBeAffiliatedOrFail();

            $lines->push([
                "name" => "Parrainage",
                "type" => "sponsorship",
                "description" => "Email du parrain: {$sponsorEmail}",
                "price" => -AffiliateProcess::AFFILIATE_DISCOUNT,
                "price_after_discount" => -AffiliateProcess::AFFILIATE_DISCOUNT
            ]);
        }

        $subTotal = $lines->sum(function ($line) {
            return $line["price"];
        });

        $total = $lines->sum(function ($line) {
            return $line["price_after_discount"];
        });

        $cartPayload = [
            "discount_code" => $request->json("discount_code"),
            "sponsor_email" => $request->json("sponsor_email"),
            "lines" => $lines,
            "sub_total" => $subTotal,
            "total" => $total
        ];

        return new JsonResponse($cartPayload);
    }
}
