<?php

use Braintree\PaymentInstrumentType;
use Braintree\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        (new CreateTransactionsTable())->down();

        Schema::create("transactions", function (Blueprint $table) {
            $table->string("id")->primary();
            $table->enum("type", [
                Transaction::SALE,
                Transaction::CREDIT
            ]);
            $table->float("amount");
            $table->float("refunded_amount");
            $table->string("currency");
            $table->enum("status", [
                Transaction::AUTHORIZATION_EXPIRED,
                Transaction::AUTHORIZED,
                Transaction::SETTLEMENT_PENDING,
                Transaction::SETTLEMENT_DECLINED,
                Transaction::FAILED,
                Transaction::GATEWAY_REJECTED,
                Transaction::PROCESSOR_DECLINED,
                Transaction::SETTLED,
                Transaction::SETTLING,
                Transaction::SUBMITTED_FOR_SETTLEMENT,
                Transaction::VOIDED
            ]);
            $table->enum("payment_instrument_type", [
                PaymentInstrumentType::ANDROID_PAY_CARD,
                PaymentInstrumentType::APPLE_PAY_CARD,
                PaymentInstrumentType::CREDIT_CARD,
                PaymentInstrumentType::MASTERPASS_CARD,
                PaymentInstrumentType::PAYPAL_ACCOUNT,
                PaymentInstrumentType::SAMSUNG_PAY_CARD,
                PaymentInstrumentType::VENMO_ACCOUNT,
                PaymentInstrumentType::VISA_CHECKOUT_CARD
            ])->nullable();
            $table->json("processor_response")->nullable();
            $table->enum("gateway_rejection_reason", [
                "application_incomplete",
                "avs",
                "avs_and_cvv",
                "cvv",
                "duplicate",
                "fraud",
                "risk_threshold",
                "three_d_secure",
                "token_issuance"
            ])->nullable();
            $table->string("refund_transaction_id")
                ->nullable()
                ->comment("The parent transaction which has been refunded by this transaction.");
            $table->string("customer_id")->nullable();
            $table->string("subscription_id")->nullable();
            $table->timestamps();

            // Foreign keys
            $table->unique("id");
            $table->foreign("refund_transaction_id")->references("id")->on("transactions");
            $table->foreign("customer_id")->references("braintree_id")->on("users");
            $table->foreign("subscription_id")->references("id")->on("subscriptions");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("transactions");

        (new CreateTransactionsTable())->up();
    }
}
