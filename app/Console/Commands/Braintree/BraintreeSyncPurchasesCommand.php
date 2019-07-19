<?php

namespace App\Console\Commands\Braintree;

use App\Console\Commands\BoxStyle;
use App\Models\Product;
use App\Services\Braintree\Models\LineItem;
use App\Services\Braintree\Models\Transaction;
use Braintree\Transaction as BraintreeTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

class BraintreeSyncPurchasesCommand extends BaseCommand
{
    use BoxStyle;

    private const CHUNK_SIZE = 100;

    protected $signature = "braintree:sync:purchases";

    /** @var Collection */
    private $purchases;


    public function handle()
    {
        DB::unprepared("truncate table purchases;");
        $this->purchases = new Collection();
        $this->makePurchases(LineItem::all());
        $this->savePurchases();
    }


    /**
     * @param LineItem[]|Collection $lineItems
     */
    public function makePurchases($lineItems) // 500
    {
        $this->line("Making purchases");
        $this->progressStart($lineItems->count());

        $productIds = Product::query()->get(["id"])->pluck("id");

        $lineItems->each(function (LineItem $lineItem) use ($productIds) {
            $this->progressAdvance();

            if (
                $lineItem->transaction === null
                || $lineItem->transaction->user === null
                || $lineItem->transaction->type !== BraintreeTransaction::SALE
                || $lineItem->transaction->refunds()->sum("amount") % 10 !== 0
                || !$productIds->contains($lineItem->product_code)
            ) {
                return;
            }


            $this->purchases->add([
                "transaction_id" => $lineItem->transaction->id,
                "line_item_id" => $lineItem->id,
                "user_id" => $lineItem->transaction->user->id,
                "product_id" => $lineItem->product_code,
                "created_at" => $lineItem->transaction->created_at,
                "updated_at" => $lineItem->transaction->updated_at
            ]);
        });

        $this->progressFinish();
    }


    public function savePurchases(): void
    {
        $this->line("Saving purchases");
        $this->progressStart($this->purchases->count());
        $this->purchases->chunk(self::CHUNK_SIZE)->each(function (Collection $lineItems) {
            DB::table("purchases")->insert($lineItems->toArray());
            $this->progressAdvance($lineItems->count());
        });
        $this->progressFinish();
    }
}
