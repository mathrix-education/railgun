<?php

namespace App\Jobs;

use App\Mails\TransactionRefundFailedMail;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Braintree\BraintreeResponse;
use App\Services\Braintree\Utils\AffiliateProcess;
use Braintree\Transaction;
use Illuminate\Support\Facades\Mail;

/**
 * Class RefundSponsorJob.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class RefundSponsorJob extends BaseJob
{
    /** @var int The refunded amount in euros */
    public const SPONSOR_REFUND = 10;

    /** @var User $affiliate */
    public $affiliate;
    /** @var User $sponsor */
    public $sponsor;
    /** @var BraintreeResponse */
    public $refundResult;


    /**
     * RefundSponsorJob constructor.
     *
     * @param User $affiliate
     * @param User $sponsor
     */
    public function __construct(User $affiliate, User $sponsor)
    {
        $this->affiliate = $affiliate;
        $this->sponsor = $sponsor;
    }


    public function handle()
    {
        /** @var Purchase $purchase */
        $purchase = $this->sponsor->purchases()
            ->orderByDesc("created_at")
            ->firstOrFail();

        if ($purchase->transaction_id === null) {
            return;
        }

        /**
         * For now, we do not have to check if the transaction has been totally refunded since or maximum total refund
         * (15€) is greater the cheapest product (>30€). Therefore, no check is present.
         *
         * @var BraintreeResponse $refundResult
         */
        $this->refundResult = Transaction::refund($purchase->transaction_id, AffiliateProcess::AFFILIATE_DISCOUNT);

        if (!$this->refundResult->success) {
            Mail::queue(new TransactionRefundFailedMail(
                $this->affiliate,
                $this->sponsor,
                $purchase->transaction_id
            ));
            // BraintreeErrorHandler::report($refundResult);
        }
    }
}
