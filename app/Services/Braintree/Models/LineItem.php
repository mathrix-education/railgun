<?php

namespace App\Services\Braintree\Models;

use App\Services\Braintree\Utils\LineItemsUtils;
use Braintree\TransactionLineItem as BraintreeLineItem;
use Mathrix\Lumen\Zero\Models\BaseModel;

class LineItem extends BaseModel
{
    public $timestamps = false;
    protected $fillable = [
        "kind",
        "name",
        "product_code",
        "product_type",
        "quantity",
        "total_amount",
        "unit_amount",
        "unit_of_measure",
        "transaction_id"
    ];


    public function product()
    {
        return $this->morphTo();
    }


    /**
     * @param BraintreeLineItem $lineItem
     * @param string $transactionId
     *
     * @return array
     */
    public static function toEloquent(BraintreeLineItem $lineItem, string $transactionId)
    {
        $data = [
            "kind" => $lineItem->kind,
            "name" => $lineItem->name,
            "product_code" => $lineItem->productCode,
            "product_type" => LineItemsUtils::getType($lineItem),
            "quantity" => $lineItem->quantity,
            "total_amount" => $lineItem->totalAmount,
            "unit_amount" => $lineItem->unitAmount,
            "unit_of_measure" => $lineItem->unitOfMeasure,
            "transaction_id" => $transactionId
        ];

        return $data;
    }


    /**
     * @param BraintreeLineItem $lineItem
     * @param string $transactionId
     *
     * @return LineItem
     */
    public static function createFromBraintreeLineItem(BraintreeLineItem $lineItem, string $transactionId)
    {
        /** @var LineItem $instance */
        $instance = self::query()->firstOrNew([
            "product_code" => $lineItem->productCode,
            "product_type" => LineItemsUtils::getType($lineItem),
            "transaction_id" => $transactionId
        ]);

        $instance->fill(self::toEloquent($lineItem, $transactionId));
        $instance->save();

        return $instance;
    }
}
