<?php

namespace App\Services\Braintree\Models;

use App\Models\Discount;
use App\Models\Product;
use App\Services\Braintree\Utils\LineItemsUtils;
use Braintree\TransactionLineItem as BraintreeLineItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * Class LineItem.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 *
 * @property-read int $id
 * @property bool $negated
 * @property string $kind
 * @property string $name
 * @property int $product_code
 * @property string $product_type
 * @property int $quantity
 * @property float $total_amount
 * @property float $unit_amount
 * @property string $unit_of_measure
 * @property string $transaction_id
 *
 * @property-read Transaction $transaction
 * @property-read Product|Discount $product
 */
class LineItem extends BaseModel
{
    use AsPivot;

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
        "user_id",
        "transaction_id"
    ];
    protected $table = "line_items";
    protected $casts = [
        "negated" => "boolean"
    ];


    /**
     * MorphTo Discount|Product
     *
     * @return MorphTo
     */
    public function product(): MorphTo
    {
        return $this->morphTo();
    }


    /**
     * BelongsTo Transaction
     *
     * @return BelongsTo
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }


    /**
     * @param BraintreeLineItem $lineItem
     * @param string $transactionId
     *
     * @return array
     */
    public static function toEloquent(BraintreeLineItem $lineItem, string $transactionId)
    {
        return [
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
