<?php

namespace App\Services\Braintree\Utils;

use App\Exceptions\InvalidSponsorException;
use App\Exceptions\SponsorNotFoundException;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Braintree\BraintreeResponse;
use App\Services\Braintree\Exceptions\BraintreeErrorHandler;
use App\Services\Braintree\Exceptions\BraintreeGatewayException;
use App\Services\Braintree\Exceptions\BraintreeProcessorException;
use App\Services\Braintree\Exceptions\DuplicatePaymentMethodNonceException;
use Braintree\Transaction;

/**
 * Class Affiliates.
 * Allow affiliation process to occur. In this class, the affiliate user design the user who want to buy products, and
 * the sponsor is the user who has already bought a product.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class AffiliateProcess
{
    /** @var int The maximum affiliates a user can have */
    public const AFFILIATES_MAX = 3;
    /** @var int The refunded amount in euros and the incrementing discount amount */
    public const AFFILIATES_FACTOR = 5;

    /** @var User The user who is currently engaging a payment process */
    private $affiliate;
    /** @var User The user who sponsors the affiliated user*/
    private $sponsor;


    /**
     * AffiliateProcess constructor.
     *
     * @param User $affiliate
     * @param string $sponsorEmail
     *
     * @throws SponsorNotFoundException
     */
    public function __construct(User $affiliate, string $sponsorEmail)
    {
        $this->affiliate = $affiliate;
        $this->sponsor = User::query()
            ->where("email", "=", $sponsorEmail)
            ->first();

        if ($this->sponsor === null) {
            throw new SponsorNotFoundException($sponsorEmail);
        }
    }


    /**
     * Check whether the sponsor can have one more affiliate.
     *
     * @return bool
     */
    public function canBeAffiliated(): bool
    {
        return 1 <= $this->sponsor->affiliate_factor && $this->sponsor->affiliate_factor <= self::AFFILIATES_MAX;
    }


    /**
     * Check whether the sponsor can have one more affiliate and throw InvalidSponsorException if he cannot.
     *
     * @return bool
     * @throws InvalidSponsorException
     */
    public function canBeAffiliatedOrFail(): bool
    {
        if (!$this->canBeAffiliated()) {
            throw new InvalidSponsorException($this->sponsor);
        }

        return true;
    }


    /**
     * Get the affiliate discount price.
     *
     * @return int
     */
    public function getDiscountAmount(): int
    {
        return self::AFFILIATES_FACTOR;
    }


    /**
     * Get the discount line item for Braintree Transaction sale request.
     *
     * @see Transaction::sale()
     * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#line_items
     * @return array
     */
    public function getDiscountLineItem(): array
    {
        return [
            "kind" => "credit",
            "name" => "Parrainage",
            "quantity" => 1,
            "totalAmount" => $this->getDiscountAmount(),
            "unitAmount" => $this->getDiscountAmount()
        ];
    }


    /**
     * Refund the sponsor after the transaction process.
     *
     * @throws BraintreeGatewayException
     * @throws BraintreeProcessorException
     * @throws DuplicatePaymentMethodNonceException
     */
    public function refundSponsor(): Transaction
    {
        /** @var Purchase $purchase */
        $purchase = $this->sponsor->purchases()
            ->orderByDesc("created_at")
            ->firstOrFail();

        /**
         * For now, we do not have to check if the transaction has been totally refunded since or maximum total refund
         * (15€) is greater the cheapest product (>30€). Therefore, no check is present.
         */
        /** @var BraintreeResponse $refundResult */
        $refundResult = Transaction::refund($purchase->transaction_id, $this->sponsor->affiliate_factor * self::AFFILIATES_FACTOR);

        if (!$refundResult->success) {
            BraintreeErrorHandler::report($refundResult);
        }

        // Increment sponsor affiliate_factor
        $this->sponsor->affiliate_factor++;
        $this->sponsor->save();

        return $refundResult->transaction;
    }
}
