<?php

namespace App\Mails;

use App\Models\User;

/**
 * Class TransactionRefundFailedMail.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class TransactionRefundFailedMail extends BaseMail
{
    private $affiliate;
    private $sponsor;
    private $transactionId;


    /**
     * TransactionRefundFailedMail constructor.
     *
     * @param User $affiliate
     * @param User $sponsor
     * @param string $transactionId
     */
    public function __construct(User $affiliate, User $sponsor, string $transactionId)
    {
        $this->affiliate = $affiliate;
        $this->sponsor = $sponsor;
        $this->transactionId = $transactionId;

        parent::__construct();
    }


    public static function mock()
    {
        $affiliate = User::random();
        $sponsor = User::random();
        $transactionId = "qsjehkSQ";

        return new self($affiliate, $sponsor, $transactionId);
    }


    /**
     * Build the mail.
     *
     * @return self
     */
    public function build()
    {
        return $this->subject("Erreur de remboursement")
            ->from("supervisor@mathrix.fr")
            ->view("mails.transactions.refund-failed", [
                "title" => "Erreur de remboursement",
                "affiliate" => $this->affiliate,
                "sponsor" => $this->sponsor,
                "transactionId" => $this->transactionId
            ]);
    }
}
