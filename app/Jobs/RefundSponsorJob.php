<?php

namespace App\Jobs;

use App\Mails\TransactionRefundFailedMail;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Braintree\BraintreeResponse;
use App\Services\Braintree\Utils\AffiliateProcess;
use Braintree\Transaction as BraintreeTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

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
        /** @var Transaction $transaction */
        $transaction = $this->sponsor->transactions()
            ->whereRaw("amount > refunded_amount + " . self::SPONSOR_REFUND)
            ->orderByDesc("created_at")
            ->firstOrFail();

        $braintreeTransaction = BraintreeTransaction::find($transaction->id);

        if (in_array($braintreeTransaction->status, [BraintreeTransaction::AUTHORIZING, BraintreeTransaction::AUTHORIZED,
            BraintreeTransaction::SUBMITTED_FOR_SETTLEMENT])) {
            // Re-schedule jop
            Queue::later(Carbon::now()->addHour(), new self($this->affiliate, $this->sponsor));

            return;
        }

        /** @var BraintreeResponse $refundResult */
        $this->refundResult = BraintreeTransaction::refund($transaction->id, AffiliateProcess::AFFILIATE_DISCOUNT);

        if ($this->refundResult->success) {
            $transaction->refund_amount += self::SPONSOR_REFUND;
            $transaction->save();
        } else {
            Mail::queue(new TransactionRefundFailedMail(
                $this->affiliate,
                $this->sponsor,
                $transaction->id
            ));
            // BraintreeErrorHandler::report($refundResult);
        }
    }
}
