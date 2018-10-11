<?php

namespace App\Services\Braintree;

use App\Services\Braintree\Models\AddOn;

/**
 * Class FamilyPack.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class FamilyPackUtils
{
    /**
     * Get add-ons for family pack.
     *
     * @param string $plan
     * @param int    $numberOfAdditionalChildren
     *
     * @return array
     */
    public static function getAddOns(string $plan, int $numberOfAdditionalChildren)
    {
        if ($numberOfAdditionalChildren < 1) {
            return [];
        }

        $addOns = [
            [
                "inheritedFromId" => $plan . "_child_30",
                "quantity" => 1,
            ],
        ];

        if ($numberOfAdditionalChildren > 1) {
            $addOns[] = [
                "inheritedFromId" => $plan . "_child_50",
                "quantity" => $numberOfAdditionalChildren - 1,
            ];
        }

        return $addOns;
    }

    /**
     * Get add-ons for family pack as line-items for initial transaction on plans with trial.
     *
     * @param string $planId
     * @param int    $numberOfAdditionalChildren
     *
     * @return array
     */
    public static function getAddOnsAsLineItemsForTrial(string $planId, int $numberOfAdditionalChildren)
    {
        $addOnChild30 = AddOn::find($planId . "_child_30");
        $addOnChild50 = AddOn::find($planId . "_child_50");

        if ($numberOfAdditionalChildren < 1) {
            return [];
        }

        $lineItems = [
            [
                "description" => $addOnChild30->description,
                "kind" => "debit",
                "name" => $addOnChild30->name,
                "quantity" => 1,
                "unitAmount" => $addOnChild30->getTrialPrice(),
                "totalAmount" => $addOnChild30->getTrialPrice(),
            ],
        ];

        if ($numberOfAdditionalChildren > 1) {
            $quantity = $numberOfAdditionalChildren - 1;
            $lineItems[] = [
                "description" => $addOnChild50->description,
                "kind" => "debit",
                "name" => $addOnChild50->name,
                "quantity" => $quantity,
                "unitAmount" => $addOnChild50->getTrialPrice(),
                "totalAmount" => $quantity * $addOnChild50->getTrialPrice(),
            ];
        }

        return $lineItems;
    }
}
