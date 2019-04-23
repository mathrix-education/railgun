<?php

namespace App\Services\Braintree\Pipelines;

use App\Events\TransactionSuccessEvent;
use App\Exceptions\DiscountExpiredException;
use App\Exceptions\DiscountNotApplicableException;
use App\Exceptions\InvalidBraintreeCustomerException;
use App\Exceptions\InvalidSponsorException;
use App\Exceptions\ProductAlreadyBoughtException;
use App\Exceptions\ProductNotBuyableException;
use App\Exceptions\SponsorNotFoundException;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Braintree\BraintreeResponse;
use App\Services\Braintree\Exceptions\BraintreeErrorHandler;
use App\Services\Braintree\Exceptions\BraintreeGatewayException;
use App\Services\Braintree\Exceptions\BraintreeProcessorException;
use App\Services\Braintree\Exceptions\DuplicatePaymentMethodNonceException;
use App\Services\Braintree\Utils\AffiliateProcess;
use Braintree\Transaction as BraintreeTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
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
 * @since 5.0.0-rc1
 */
class CartPipeline
{
    public const MODE_MACHINE_2019_END = "2019-07-31 00:00:00";

    /** @var $request Request The Illuminate HTTP request */
    private $request;
    /** @var User $customer The customer. */
    private $customer;
    /** @var EloquentCollection|Product[] The products. */
    private $products;
    /** @var Discount|null $discount The discount, if any. */
    private $discount;
    /** @var AffiliateProcess|null $affiliateProcess The affiliate process, if any. */
    private $affiliateProcess;

    /** @var BraintreeResponse $saleResult The Braintree sale result. */
    private $saleResult;


    /**
     * Cart constructor.
     *
     * @param Request $request
     *
     * @throws BraintreeGatewayException
     * @throws BraintreeProcessorException
     * @throws DiscountExpiredException
     * @throws DiscountNotApplicableException
     * @throws DuplicatePaymentMethodNonceException
     * @throws InvalidSponsorException
     * @throws ProductAlreadyBoughtException
     * @throws ProductNotBuyableException
     * @throws SponsorNotFoundException
     * @throws ValidationException
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        // Validate cart content
        $validator = ValidatorFacade::make($this->request->all(), [
            "cart" => "required",
            "cart.*" => "exists:products,id",
            "payment_method_nonce" => "required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            throw new ValidationException($errors, "Model data failed to pass validation.");
        }

        $this->setCustomer($this->request->user());
        $this->setProducts($this->request->json("cart"));
        $this->setDiscount($this->request->json("discount"));
        $this->setAffiliate($this->request->json("sponsor_email"));
    }


    /**
     * Set the discount based on its code.
     *
     * @param string $discountCode
     *
     * @return CartPipeline
     * @throws DiscountExpiredException
     * @throws DiscountNotApplicableException
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
     * @param string|null $sponsorEmail
     *
     * @throws SponsorNotFoundException
     * @throws InvalidSponsorException
     */
    public function setAffiliate(string $sponsorEmail = null)
    {
        if ($sponsorEmail !== null) {
            $this->affiliateProcess = new AffiliateProcess($this->customer, $sponsorEmail);
            $this->affiliateProcess->canBeAffiliatedOrFail();
        }
    }


    /**
     * Set the cart products.
     *
     * @param int[] $productIds
     *
     * @return $this
     *
     * @throws ProductAlreadyBoughtException
     * @throws ProductNotBuyableException
     */
    public function setProducts(array $productIds)
    {
        $productIds = array_unique($productIds);
        $this->products = new Collection();

        foreach ($productIds as $productId) {
            /** @var Product $product */
            $product = Product::query()->findOrFail($productId);

            // Product price is null
            if ($product->price === null) {
                throw new ProductNotBuyableException($product);
            }

            // Customer has already purchased the product
            if ($this->customer->hasPurchased($product)) {
                throw new ProductAlreadyBoughtException($this->customer, $product);
            }

            $this->products->push($product);
        }

        return $this;
    }


    /**
     * Set the cart customer.
     *
     * @param User $customer
     *
     * @return CartPipeline
     * @throws BraintreeGatewayException
     * @throws BraintreeProcessorException
     * @throws DuplicatePaymentMethodNonceException
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
        $lineItems = new EloquentCollection();

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

        $amount = $lineItems->sum(function ($lineItem) {
            return $lineItem["totalAmount"];
        });
        $discountAmount = $lineItems->sum(function ($lineItem) {
            return $lineItem["discountAmount"] ?? 0;
        });

        // Add affiliate line item
        if ($this->affiliateProcess !== null && $this->affiliateProcess->canBeAffiliated()) {
            $lineItems->add($this->affiliateProcess->getDiscountLineItem());
            $amount -= $this->affiliateProcess->getDiscountAmount(); // Reduce the cart amount
            $discountAmount += $this->affiliateProcess->getDiscountAmount(); // Increase discount amount
        }

        $saleData = [
            "amount" => $amount,
            "customerId" => $this->customer->braintree_id,
            "discountAmount" => $discountAmount,
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
     * @return JsonResponse
     * @throws BraintreeGatewayException
     * @throws BraintreeProcessorException
     * @throws DuplicatePaymentMethodNonceException
     * @throws ValidationException
     * @throws InvalidBraintreeCustomerException
     */
    public function process()
    {
        $this->customer->requireBraintreeCustomer();

        // Process the cart in a Braintree transaction
        $this->saleResult = BraintreeTransaction::sale($this->getTransactionPayload());

        if (!$this->saleResult->success) {
            $this->customer->record("braintree", "sale", "error", $this->saleResult->jsonSerialize());

            BraintreeErrorHandler::report($this->saleResult);
        }

        $this->finalize();

        $products = Product::query()
            ->whereIn("id", $this->products->pluck("id"))
            ->get();

        return new JsonResponse($products);
    }


    /**
     * Deliver pack products.
     *
     * @param Product $pack
     * @param BraintreeTransaction $transaction
     */
    private function deliverPackProducts(Product $pack, BraintreeTransaction $transaction)
    {
        $pack->items->each(function (Product $item) use ($transaction) {
            if ($item->type === "premium") {
                // Premium product
                $this->customer->premium_bypass_expires_at = new Carbon(self::MODE_MACHINE_2019_END);
                $this->customer->save();
            } else {
                $item->deliver($this->customer, $transaction);
            }
        });
    }


    /**
     * Finalize the transaction
     * - Unlock the products for the user
     * - Decrement the discount counter if needed
     * - Emit the TransactionSuccessEvent
     */
    private function finalize()
    {
        Transaction::createFromBraintreeTransaction($this->saleResult->transaction);

        // Insert new purchase records
        $this->products->each(function (Product $product) {
            // Handle packs items dispatching
            if ($product->type === "pack") {
                $this->deliverPackProducts($product, $this->saleResult->transaction);
            }

            $product->deliver($this->customer, $this->saleResult->transaction);
        });

        // Decrement discount counter if necessary
        if ($this->discount !== null && !in_array($this->discount->counter, [null, -1])) {
            $this->discount->counter--;
            $this->discount->save();
        }

        // Refund sponsor if necessary
        if ($this->affiliateProcess !== null && $this->affiliateProcess->canBeAffiliated()) {
            $this->affiliateProcess->refundSponsor();
        }

        $this->customer->save();

        event(new TransactionSuccessEvent($this->saleResult->transaction));
    }
}
