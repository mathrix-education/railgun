<?php
namespace App\Services\Braintree;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Cart
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
    }


    /**
     * @param User $customer
     *
     * @throws Http400InvalidRequestException
     */
    public function setCustomer(User $customer)
    {
        if (!$customer->isBraintreeCustomer()) {
            throw new Http400InvalidRequestException("User `id` {$customer->id} is not valid Braintree customer");
        }
        $this->customer = $customer;
    }


    /**
     * @param int[] $productIds
     *
     * @throws Http400InvalidRequestException
     */
    public function setCart(array $productIds)
    {
        foreach ($productIds as $productId) {
            $product = Product::where("id", "=", $productId)->firstOrFail();

            if ($this->customer->hasPurchased($product)) {
                throw new Http400InvalidRequestException("User `id` {$this->customer->id} has already purchased"
                    . " product `id` {$product->id}");
            }
        }
    }


    /**
     * @param string $discountCode
     *
     * @return Cart
     * @throws Http400InvalidRequestException
     */
    public function setDiscount(string $discountCode)
    {
        /** @var Discount $discount */
        $discount = Discount::where("code", "=", $discountCode)->firstOrFail();

        // Check for expiration
        if ($discount->isExpired()) {
            throw new Http400InvalidRequestException("Discount with code `$discountCode` has expired");
        }
        // Expiration OK

        // Check if applicable
        $isApplicable = false;

        /** @var Product $product */
        foreach ($this->products as $product) {
            if ($discount->isApplicableOn($product)) {
                $isApplicable = true;
                break;
            }
        }

        if (!$isApplicable) {
            throw new Http400InvalidRequestException("Discount with code `$discountCode` is not applicable on current cart");
        }
        // Application OK

        $this->discount = $discount;

        return $this;
    }


    public function getBraintreeSaleRequest()
    {
        return [

        ];
    }
}
