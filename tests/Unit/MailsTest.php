<?php

namespace Tests\Unit;

use App\Mails\PasswordForgottenMail;
use App\Mails\PasswordResetMail;
use App\Mails\TransactionRefundMail;
use App\Mails\TransactionSuccessMail;
use Tests\BaseTestCase;

/**
 * Class MailsTest.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since
 *
 * @copyright Mathrix Education SA
 */
class MailsTest extends BaseTestCase
{
    public function testPasswordForgotten()
    {
        $mail = PasswordForgottenMail::mock()
            ->build();
        $this->assertInstanceOf(PasswordForgottenMail::class, $mail);
    }

    public function testPasswordReset()
    {
        $mail = PasswordResetMail::mock()
            ->build();
        $this->assertInstanceOf(PasswordResetMail::class, $mail);
    }

    public function testTransactionRefundSuccess()
    {
        $mail = TransactionRefundMail::mock()
            ->build();
        $this->assertInstanceOf(TransactionRefundMail::class, $mail);
    }

    public function testTransactionSuccess()
    {
        $mail = TransactionSuccessMail::mock()
            ->build();
        $this->assertInstanceOf(TransactionSuccessMail::class, $mail);
    }
}
