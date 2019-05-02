<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PackFixCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class PackFixCommand extends BaseCommand
{
    protected $signature = "pack:fix";


    public function handle()
    {
        $this->line("Welcome to Pack Fix command, designed for the guys who cannot buy the right pack.");
        $identifier = $this->ask("Enter the user email address, braintree_id or id:");

        /** @var User $user */
        $user = User::query()->where("email", "=", $identifier)
            ->first();

        if ($user === null) {
            $this->line("Cannot find any user matching those criteria");

            return;
        }

        /** @var Product $pack */
        $pack = $user->products()
            ->where("type", "=", "pack")
            ->first();
        if ($pack === null) {
            $this->line("This user has not any pack registered");

            return;
        }

        $this->line("We found that the user got the pack \"{$pack->name}\", which contains:");
        $packData = $pack->items->map(function (Product $training) {
            return [
                "product_id" => $training->id,
                "name" => $training->name
            ];
        });
        $this->table(["id", "name"], $packData);


        $packs = Product::query()
            ->where("type", "=", "pack")
            ->where("id", "!=", $pack->id)
            ->get();

        $newPackName = $this->choice(
            "Select the new pack to apply to this user:",
            $packs->pluck("name")->toArray()
        );


        // Print the existing purchases
        $oldPackProductsIds = array_merge([$pack->id], Arr::pluck($packData, "product_id"));
        $purchasesToPatch = $user->purchases()
            ->whereIn("product_id", $oldPackProductsIds)
            ->with("product")
            ->get();
        $purchasesToPatchData = $purchasesToPatch->map(function (Purchase $purchase) {
            return [
                "purchase_id" => $purchase->id,
                "transaction_id" => $purchase->transaction_id,
                "product" => $purchase->product->name
            ];
        });

        $this->line("The following purchases wil be DELETED:");
        $this->table(["purchase_id", "transaction_id", "product"], $purchasesToPatchData);


        // Build the new purchases
        /** @var Product $newPack */
        $newPack = Product::query()
            ->where("name", "=", $newPackName)
            ->firstOrFail();
        $newPurchasesProductIds = array_merge([$newPack->id], $newPack->items->pluck("id")->toArray());
        $purchaseTemplate = Arr::only(
            $purchasesToPatch->first()->toArray(),
            ["transaction_id", "user_id", "created_at", "updated_at"]
        );

        // Fix dates
        $purchaseTemplate["created_at"] = Carbon::createFromTimeString($purchaseTemplate["created_at"])->format("Y-m-d H:i:s");
        $purchaseTemplate["updated_at"] = Carbon::createFromTimeString($purchaseTemplate["updated_at"])->format("Y-m-d H:i:s");

        $newPurchasesData = array_map(function ($productId) use ($purchaseTemplate) {
            return array_merge($purchaseTemplate, ["product_id" => $productId]);
        }, $newPurchasesProductIds);
        $newPurchasesTableData = array_map(function ($purchaseData) {
            return [
                "purchase_id" => "XXXX",
                "transaction_id" => $purchaseData["transaction_id"],
                "product" => Product::find($purchaseData["product_id"])->name
            ];
        }, $newPurchasesData);

        $this->line("The following purchases will be APPLIED:");
        $this->table(array_keys($newPurchasesTableData[0]), $newPurchasesTableData);

        $confirm = $this->confirm("Please confirm the procedure");
        if (!$confirm) {
            return;
        }

        DB::table("purchases")->insert($newPurchasesData);
        DB::table("purchases")->whereIn("id", $purchasesToPatchData->pluck("purchase_id"))->delete();
        $user->invalidate();

        $this->line("Done.");
    }
}
