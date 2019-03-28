<?php

namespace App\Services\Braintree\Pipelines;

use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class CartPipeline.
 * Used to generate \Braintree\Transaction::sale() request data.
 *
 * @see \Braintree\Transaction::sale()
 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class CartPipeline
{
    /** @var User $customer */
    private $customer;
    /** @var Collection|Product[] */
    private $products;
    /** @var Discount|null $discount */
    private $discount;


    /**
     * Cart constructor.
     */
    public function __construct()
    {
        $this->products = new Collection();
    }


    /**
     * Set the cart customer.
     *
     * @param User $customer
     *
     * @return CartPipeline
     */
    public function setCustomer(User $customer)
    {
        $this->customer = $customer;

        return $this;
    }


    /**
     * Get the cart products.
     *
     * @return Product[]|Collection
     */
    public function getProducts()
    {
        return $this->products;
    }


    /**
     * Set the cart products.
     *
     * @param int[] $productIds
     *
     * @throws Http400BadRequestException
     *
     * @return CartPipeline
     */
    public function setProducts(array $productIds)
    {
        $productIds = array_unique($productIds); // Remove double ids

        foreach ($productIds as $productId) {
            /** @var Product $product */
            $product = Product::query()->findOrFail($productId);

            if ($product->price === null) {
                throw new Http400BadRequestException(
                    ["product" => $product],
                    "Product `id` {$this->customer->id} cannot be purchased"
                );
            }

            if ($this->customer->hasPurchased($product)) {
                throw new Http400BadRequestException(
                    ["product" => $product],
                    "User `id` {$this->customer->id} has already purchased product `id` {$product->id}"
                );
            }

            $this->products->add($product);
        }

        return $this;
    }


    /**
     * Get the discount.
     *
     * @return Discount|null
     */
    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }


    /**
     * Set the discount based on its code.
     *
     * @param string $discountCode
     *
     * @return CartPipeline
     */
    public function setDiscount(string $discountCode = null)
    {
        if ($discountCode !== null) {
            $this->discount = Discount::query()
                ->where("code", "=", $discountCode)
                ->firstOrFail();
        }

        return $this;
    }


    /**
     * Get the \Braintree\Transaction::sale() request data.
     *
     * @see \Braintree\Transaction::sale()
     * @see https://developers.braintreepayments.com/reference/request/transaction/sale/php
     *
     * @return array
     * @throws Http400BadRequestException
     */
    public function getBraintreeSaleRequest()
    {
        $this->validate();

        $lineItems = new Collection();

        /** @var Product $product */
        foreach ($this->products as $product) {
            $lineItem = [
                "description" => $product->description,
                "kind" => "debit",
                "name" => $product->name,
                "productCode" => $product->id,
                "quantity" => 1,
                "totalAmount" => $product->getNewPrice($this->discount),
                "unitAmount" => $product->price,
                "unitOfMeasure" => "eur",
            ];

            if ($this->discount !== null && $this->discount->isApplicableOn($product)) {
                $lineItem["discountAmount"] = $product->price - $product->getNewPrice($this->discount);
            }

            $lineItems->add($lineItem);
        }

        $saleData = [
            "amount" => $lineItems->sum(function ($lineItem) {
                return $lineItem["totalAmount"];
            }),
            "customerId" => $this->customer->braintree_id,
            "discountAmount" => $lineItems->sum(function ($lineItem) {
                return $lineItem["discountAmount"] ?? 0;
            }),
            "lineItems" => $lineItems->toArray(),
            "options" => [
                "submitForSettlement" => true,
            ],
        ];

        return $saleData;
    }


    /**
     * Validate all properties before engaging the Braintree request data.
     * @throws Http400BadRequestException
     */
    private function validate()
    {
        // Check customer existence
        if ($this->customer === null) {
            throw new Http400BadRequestException(null, "No customer set.");
        }

        // Check customer validity
        $this->customer->requireBraintreeCustomer();

        if ($this->discount !== null) {
            // Check for discount expiration
            if ($this->discount->isExpired()) {
                throw new Http400BadRequestException(null, "Discount has expired");
            }

            // Check discount application
            if (!$this->discount->isApplicableBleOnAtLeastOneOf($this->products)) {
                throw new Http400BadRequestException(null, "Discount is not applicable on current cart");
            }
        }
    }
}
