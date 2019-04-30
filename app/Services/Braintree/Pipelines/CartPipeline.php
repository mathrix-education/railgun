<?php

namespace App\Services\Braintree\Pipelines;

use App\Events\TransactionSuccessEvent;
use App\Exceptions\DiscountExpiredException;
use App\Exceptions\DiscountNotApplicableException;
use App\Exceptions\ImmutableException;
use App\Exceptions\InvalidSponsorException;
use App\Exceptions\ProductAlreadyBoughtException;
use App\Exceptions\ProductNotBuyableException;
use App\Exceptions\SponsorNotFoundException;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\Braintree\BraintreeResponse;
use App\Services\Braintree\Exceptions\BraintreeErrorHandler;
use App\Services\Braintree\Exceptions\BraintreeGatewayException;
use App\Services\Braintree\Exceptions\BraintreeProcessorException;
use App\Services\Braintree\Exceptions\DuplicatePaymentMethodNonceException;
use App\Services\Braintree\Utils\AffiliateProcess;
use App\Services\Braintree\Utils\Formatter;
use Braintree\Transaction as BraintreeTransaction;
use Faker\Provider\Uuid;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
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
class CartPipeline extends BasePipeline
{
    /** @var EloquentCollection|Product[] The products. */
    private $products;
    /** @var Discount|null $discount The discount, if any. */
    private $discount;
    /** @var AffiliateProcess|null $affiliateProcess The affiliate process, if any. */
    private $affiliateProcess;
    /** @var BraintreeResponse $saleResult The Braintree sale result. */
    private $saleResult;


    /**
     * @throws DiscountExpiredException
     * @throws DiscountNotApplicableException
     * @throws InvalidSponsorException
     * @throws ProductAlreadyBoughtException
     * @throws ProductNotBuyableException
     * @throws SponsorNotFoundException
     * @throws ValidationException
     */
    protected function validate(): void
    {
        // Validate cart content
        $validator = ValidatorFacade::make($this->request->all(), [
            "cart" => "required",
            "cart.*" => "exists:products,id"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            throw new ValidationException($errors, "Model data failed to pass validation.");
        }

        $this->customer = $this->request->user()->fresh();
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
    private function setDiscount(string $discountCode = null)
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
    private function setAffiliate(string $sponsorEmail = null)
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
    private function setProducts(array $productIds)
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
     * Get the Braintree Transaction payload for this cart.
     *
     * @return array
     */
    protected function getTransactionPayload()
    {
        $lineItems = new EloquentCollection();
        $amount = 0;

        /** @var Product $product */
        foreach ($this->products as $product) {
            $amount += $product->getNewPrice($this->discount);

            $lineItem = [
                "description" => $product->description,
                "kind" => "debit",
                "name" => $product->name,
                "productCode" => $product->id,
                "quantity" => 1, // We only sell digital products
                "totalAmount" => $product->price,
                "unitAmount" => $product->price,
                "unitOfMeasure" => "eur",
            ];

            if ($this->discount !== null && $this->discount->isApplicableOn($product)) {
                $lineItem["discountAmount"] = $product->price - $product->getNewPrice($this->discount);
            }

            $lineItems->add($lineItem);
        }

        $discountAmount = $lineItems->sum(function ($lineItem) {
            return $lineItem["discountAmount"] ?? 0;
        });

        if ($discountAmount > 0) {
            $lineItems->add([
                "description" => "Code promo: {$this->discount->code}",
                "kind" => "credit",
                "name" => $this->discount->code,
                "productCode" => $this->discount->id,
                "quantity" => 1, // We only sell digital products
                "totalAmount" => $discountAmount,
                "unitAmount" => $discountAmount,
                "unitOfMeasure" => "eur",
            ]);
        }

        // Add affiliate line item
        if ($this->affiliateProcess !== null && $this->affiliateProcess->canBeAffiliated()) {
            $lineItems->add($this->affiliateProcess->getDiscountLineItem());
            $amount -= $this->affiliateProcess->getDiscountAmount(); // Reduce the cart amount
            $discountAmount += $this->affiliateProcess->getDiscountAmount(); // Increase discount amount
        }

        $saleData = [
            "orderId" => Uuid::uuid(),
            "amount" => $amount,
            "paymentMethodNonce" => $this->request->json("payment_method_nonce"),
            "customerId" => $this->customer->braintree_id,
            "discountAmount" => $discountAmount,
            "lineItems" => $lineItems->toArray(),
            "merchantAccountId" => env("BRAINTREE_MERCHANT_ACCOUNT"),
            "options" => [
                "storeInVaultOnSuccess" => true,
                "submitForSettlement" => true,
            ],
        ];

        // Handle customer creation on the fly
        if ($this->customer->isBraintreeCustomer()) {
            $saleData["customerId"] = $this->customer->braintree_id;
        } else {
            $saleData["customer"] = $this->customer->getBraintreeCustomerCreationPayload(); // Save the generated braintree_id
        }

        return $saleData;
    }


    /**
     * Process the Braintree Transaction.
     *
     * @throws BraintreeGatewayException
     * @throws BraintreeProcessorException
     * @throws DuplicatePaymentMethodNonceException
     * @throws DuplicatePaymentMethodNonceException
     * @throws ImmutableException
     */
    protected function work(): void
    {
        // Process the cart in a Braintree transaction
        /** @var BraintreeResponse $saleResult */
        $this->saleResult = BraintreeTransaction::sale($this->getTransactionPayload());

        if (!$this->saleResult->success) {
            $this->customer->record("braintree", "sale", "error", $this->saleResult->jsonSerialize());

            BraintreeErrorHandler::report($this->saleResult);
        }
    }


    /**
     * Finalize the transaction
     * - Unlock the products for the user
     * - Decrement the discount counter if needed
     * - Emit the TransactionSuccessEvent
     */
    protected function finalize(): JsonResponse
    {
        if (isset($this->saleResult->transaction->customerDetails->id) && !$this->customer->isBraintreeCustomer()) {
            $this->customer->braintree_id = $this->saleResult->transaction->customerDetails->id;
            $this->customer->save();
        }

        Transaction::createFromBraintreeTransaction($this->saleResult->transaction);

        // Insert new purchase records
        $this->products->each(function (Product $product) {
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

        return new JsonResponse(Formatter::cast($this->saleResult->transaction->jsonSerialize()));
    }
}
