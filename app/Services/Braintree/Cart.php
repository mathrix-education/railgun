<?php
namespace App\Services\Braintree;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Cart
 * Used to generate \Braintree\Transaction::sale() request data
 *
 * @see \Braintree\Transaction::sale()
 * @see https://developers.braintreepayments.com/reference/request/transaction/sale/php
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Services\Braintree
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class Cart
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
     * Set the cart customer
     *
     * @param User $customer
     *
     * @return Cart
     */
    public function setCustomer(User $customer)
    {
        $this->customer = $customer;
        return $this;
    }


    /**
     * Get the cart products
     *
     * @return Product[]|Collection
     */
    public function getProducts()
    {
        return $this->products;
    }


    /**
     * Set the cart products
     *
     * @param int[] $productIds
     *
     * @return Cart
     * @throws Http400InvalidRequestException
     */
    public function setProducts(array $productIds)
    {
        $productIds = array_unique($productIds); // Remove double ids

        foreach ($productIds as $productId) {
            $product = Product::where("id", "=", $productId)->firstOrFail();

            if ($product->price === null) {
                throw new Http400InvalidRequestException("Product `id` {$this->customer->id} cannot be "
                    . "purchased");
            }

            if ($this->customer->hasPurchased($product)) {
                throw new Http400InvalidRequestException("User `id` {$this->customer->id} has already "
                    . "purchased product `id` {$product->id}");
            }

            $this->products->add($product);
        }

        return $this;
    }


    /**
     * Get the discount
     *
     * @return Discount|null
     */
    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }


    /**
     * Set the discount based on its code
     *
     * @param string $discountCode
     *
     * @return Cart
     */
    public function setDiscount(string $discountCode = null)
    {
        if ($discountCode !== null) {
            $this->discount = Discount::where("code", "=", $discountCode)->firstOrFail();;
        }

        return $this;
    }


    /**
     * Validate all properties before engaging the Braintree request data
     *
     * @throws Http400InvalidRequestException
     */
    private function validate()
    {
        // Check customer existence
        if ($this->customer === null) {
            throw new Http400InvalidRequestException("Not customer set in the cart");
        }

        // Check customer validity
        $this->customer->requireBraintreeCustomer();

        if ($this->discount !== null) {
            // Check for discount expiration
            if ($this->discount->isExpired()) {
                throw new Http400InvalidRequestException("Discount with code `{$this->discount->code}` has expired");
            }

            // Check discount application
            if (!$this->discount->isApplicableBleOnAtLeastOneOf($this->products)) {
                throw new Http400InvalidRequestException("Discount with code `{$this->discount->code}` is not"
                    . " applicable on current cart");
            }
        }
    }


    /**
     * Get the \Braintree\Transaction::sale() request data
     *
     * @see \Braintree\Transaction::sale()
     * @see https://developers.braintreepayments.com/reference/request/transaction/sale/php
     * @return array
     * @throws Http400InvalidRequestException
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
                "unitOfMeasure" => "eur"
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
                "submitForSettlement" => true
            ]
        ];

        return $saleData;
    }
}
