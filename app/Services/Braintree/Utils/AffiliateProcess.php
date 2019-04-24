<?php

namespace App\Services\Braintree\Utils;

use App\Exceptions\InvalidSponsorException;
use App\Exceptions\SponsorNotFoundException;
use App\Jobs\RefundSponsorJob;
use App\Models\User;
use Braintree\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;

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
    /** @var int The discount amount */
    public const AFFILIATE_DISCOUNT = 10;
    /** @var string The default sponsor email (which always works) */
    public const DEFAULT_SPONSOR = "contact@mathrix.fr";
    /** @var int The delay (in hours) before trying to refund the sponsor */
    public const SPONSOR_REFUND_DELAY = 3;

    /** @var User The user who is currently engaging a payment process */
    private $affiliate;
    /** @var User The user who sponsors the affiliated user */
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
        if ($this->sponsor->email === self::DEFAULT_SPONSOR) {
            return true;
        }

        $validSponsor = $this->sponsor->purchases()
            ->whereBetween("created_at", [
                Carbon::createFromDate(2019, 1, 1),
                Carbon::now()
            ])
            ->get()
            ->isNotEmpty();
        $notHimself = $this->sponsor->email !== $this->affiliate->email;

        return $notHimself && $validSponsor;
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
        return self::AFFILIATE_DISCOUNT;
    }


    /**
     * Get the discount line item for Braintree Transaction sale request.
     *
     * @return array
     * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#line_items
     * @see Transaction::sale()
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
     */
    public function refundSponsor()
    {
        if ($this->sponsor->email !== self::DEFAULT_SPONSOR) {
            Queue::later(
                Carbon::now()->addHours(self::SPONSOR_REFUND_DELAY),
                new RefundSponsorJob($this->affiliate, $this->sponsor)
            );
        }
    }
}
