<?php

declare(strict_types=1);

namespace App\Services\Braintree\Utils;

use App\Exceptions\InvalidSponsorException;
use App\Exceptions\SponsorNotFoundException;
use App\Jobs\RefundSponsorJob;
use App\Models\User;
use Braintree\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;

/**
 * Allow affiliation process to occur. In this class, the affiliate user design the user who want to buy products, and
 * the sponsor is the user who has already bought a product.
 */
class AffiliateProcess
{
    public const AFFILIATE_DISCOUNT   = 10;
    public const DEFAULT_SPONSOR      = 'contact@mathrix.fr';
    public const SPONSOR_REFUND_DELAY = 3;

    /** @var User The user who is currently engaging a payment process */
    private $affiliate;
    /** @var User The user who sponsors the affiliated user */
    private $sponsor;

    /**
     * @throws SponsorNotFoundException
     */
    public function __construct(User $affiliate, string $sponsorEmail)
    {
        $this->affiliate = $affiliate;
        $this->sponsor   = User::query()
            ->where('email', '=', $sponsorEmail)
            ->first();

        if ($this->sponsor === null) {
            throw new SponsorNotFoundException($sponsorEmail);
        }
    }

    /**
     * Check whether the sponsor can have one more affiliate and throw InvalidSponsorException if he cannot.
     *
     * @throws InvalidSponsorException
     */
    public function canBeAffiliatedOrFail() : bool
    {
        if (! $this->canBeAffiliated()) {
            throw new InvalidSponsorException($this->sponsor);
        }

        return true;
    }

    /**
     * Check whether the sponsor can have one more affiliate.
     */
    public function canBeAffiliated() : bool
    {
        if ($this->sponsor->email === self::DEFAULT_SPONSOR) {
            return true;
        }

        $validSponsor = $this->sponsor->purchases()
            ->whereBetween('created_at', [
                Carbon::createFromDate(2019, 1, 1),
                Carbon::now(),
            ])
            ->get()
            ->isNotEmpty();
        $notHimself   = $this->sponsor->email !== $this->affiliate->email;

        return $notHimself && $validSponsor;
    }

    /**
     * Get the discount line item for Braintree Transaction sale request.
     *
     * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#line_items
     * @see Transaction::sale()
     *
     * @return array
     */
    public function getDiscountLineItem() : array
    {
        return [
            'kind' => 'credit',
            'name' => 'Parrainage',
            'quantity' => 1,
            'totalAmount' => $this->getDiscountAmount(),
            'unitAmount' => $this->getDiscountAmount(),
        ];
    }

    /**
     * Get the affiliate discount price.
     */
    public function getDiscountAmount() : int
    {
        return self::AFFILIATE_DISCOUNT;
    }

    /**
     * Refund the sponsor after the transaction process.
     */
    public function refundSponsor() : void
    {
        if ($this->sponsor->email === self::DEFAULT_SPONSOR) {
            return;
        }

        Queue::later(
            Carbon::now()->addHours(self::SPONSOR_REFUND_DELAY),
            new RefundSponsorJob($this->affiliate, $this->sponsor)
        );
    }
}
