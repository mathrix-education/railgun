<?php

namespace App\Services\Braintree\Pipelines;

use App\Events\TransactionSuccessEvent;
use App\Exceptions\DiscountExpiredException;
use App\Exceptions\DiscountNotApplicableException;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\ValidationException;

/**
 * Class CartPipeline.
 * Used to generate \Braintree\Transaction::sale() request data.
 *
 * @see \Braintree\Transaction::sale()
 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class CartPipeline
{
    /** @var $request Request The Illuminate HTTP request */
    private $request;
    /** @var User $customer The customer. */
    private $customer;
    /** @var Collection|Product[] The products. */
    private $products;
    /** @var Discount|null $discount The discount, if any. */
    private $discount;

    /** @var Error|Successful $saleResult The Braintree sale result. */
    private $saleResult;


    /**
     * Cart constructor.
     *
     * @param Request $request
     *
     * @throws Http400BadRequestException
     * @throws ValidationException
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        // Validate cart content
        $validator = ValidatorFacade::make($this->request->all(), [
            "cart" => "required",
            "cart.*" => "exists:products,id",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            throw new ValidationException($errors, "Model data failed to pass validation.");
        }

        $this->setCustomer($this->request->user());
        $this->setProducts($this->request->json("cart"));
        $this->setDiscount($this->request->json("discount"));
    }


    /**
     * Set the discount based on its code.
     *
     * @param string $discountCode
     *
     * @return CartPipeline
     * @throws Http400BadRequestException
     */
    public function setDiscount(string $discountCode = null)
    {
        if ($discountCode !== null) {
            $this->discount = Discount::query()
                ->where("code", "=", $discountCode)
                ->firstOrFail();

            // Check for discount request expiration
            if ($this->discount->isExpired()) {
                throw new DiscountExpiredException($this->discount);
            }

            // Check discount application
            if (!$this->discount->isApplicableBleOnAtLeastOneOf($this->products)) {
                throw new DiscountNotApplicableException($this->discount);
            }
        }

        return $this;
    }


    /**
     * Set the cart products.
     *
     * @param int[] $productIds
     *
     * @return $this
     */
    public function setProducts(array $productIds)
    {
        $this->products = collect(array_unique($productIds))
            ->map(function ($productId) {
                /** @var Product $product */
                $product = Product::query()->findOrFail($productId);

                // Product price is null
                if ($product->price === null) {
                    throw new Http400BadRequestException(
                        ["product" => $product],
                        "Product `id` {$this->customer->id} cannot be purchased"
                    );
                }

                // Customer has already purchased the product
                if ($this->customer->hasPurchased($product)) {
                    throw new Http400BadRequestException(
                        ["product" => $product],
                        "User `id` {$this->customer->id} has already purchased product `id` {$product->id}"
                    );
                }

                return $product;
            });

        return $this;
    }


    /**
     * Set the cart customer.
     *
     * @param User $customer
     *
     * @return CartPipeline
     * @throws Http400BadRequestException
     */
    public function setCustomer(User $customer)
    {
        $this->customer = $customer;

        // Create customer if needed
        if ($this->request->has("payment_method_nonce") && !$this->customer->isBraintreeCustomer()) {
            $paymentMethodNonce = $this->request->input("payment_method_nonce");
            $this->customer->createBraintreeCustomer($paymentMethodNonce);
        }

        return $this;
    }


    /**
     * Get the Braintree Transaction payload for this cart.
     *
     * @return array
     */
    public function getTransactionPayload()
    {
        $lineItems = new Collection();

        /** @var Product $product */
        foreach ($this->products as $product) {
            $lineItem = [
                "description" => $product->description,
                "kind" => "debit",
                "name" => $product->name,
                "productCode" => $product->id,
                "quantity" => 1, // We only sell digital products
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
     * Process the Braintree Transaction.
     *
     * @throws Http400BadRequestException
     * @throws ValidationException
     */
    public function process()
    {
        // Check customer existence and validity
        if ($this->customer === null) {
            throw new Http400BadRequestException(null, "No customer set.");
        }
        $this->customer->requireBraintreeCustomer();

        // Process the cart in a Braintree transaction
        $this->saleResult = Transaction::sale($this->getTransactionPayload());

        if (!$this->saleResult->success) {
            $this->customer->record("braintree", "sale", "error", $this->saleResult->jsonSerialize());
            throw new Http400BadRequestException($this->saleResult->jsonSerialize(), "Sale failed");
        }

        $this->finalize();

        $products = Product::query()
            ->whereIn("id", $this->products->pluck("id"))
            ->get();

        return new JsonResponse($products);
    }


    /**
     * Finalize the transaction
     * - Unlock the products for the user
     * - Decrement the discount counter if needed
     * - Emit the TransactionSuccessEvent
     */
    private function finalize()
    {
        // Insert new purchase records
        $this->products->each(function ($product) {
            $purchase = new Purchase([
                "user_id" => $this->customer->id,
                "product_id" => $product->id,
            ]);
            $purchase->save();
        });

        // Decrement discount counter if necessary
        if ($this->discount !== null && !in_array($this->discount->counter, [null, -1])) {
            $this->discount->counter--;
            $this->discount->save();
        }

        event(new TransactionSuccessEvent($this->saleResult->transaction));
    }
}
