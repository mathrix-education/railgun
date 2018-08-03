<?php
namespace App\Services\Braintree;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Discount;
use App\Models\Interfaces\Purchasable;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class Cart
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Services\Braintree
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class Cart
{
    private $products;
    private $discount;
    private $referal;


    /**
     * Cart constructor.
     *
     * @param $items
     * @param $discountCode
     * @param $referralEmail
     *
     * @throws Http400InvalidRequestException
     */
    public function __construct(array $items, string $discountCode = null, string $referralEmail = null)
    {
        foreach ($items as $item) {
            if (empty($item["purchasable_id"]) || empty($item["purchasable_type"])) {
                throw new Http400InvalidRequestException("Missing purchasable_id or purchasable_type");
            }

            $this->products[] = $this->getProduct($item["purchasable_id"], $item["purchasable_type"]);
        }

        if (!empty($discountCode)) {
            /** @var Discount $discount */
            $discount = Discount::where("code", "=", $discountCode)->firstOrFail();
        }
    }


    /**
     * @param int $id
     * @param string $type
     *
     * @return Purchasable
     */
    private function getProduct(int $id, string $type)
    {
        /** @var Purchasable $purchasableClass */
        $purchasableClass = Relation::getMorphedModel($type);
        return $purchasableClass->where("id", "=", $id)->firstOrFail();
    }
}
